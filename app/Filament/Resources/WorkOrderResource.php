<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WorkOrderResource\Pages;
use App\Filament\Resources\WorkOrderResource\RelationManagers;
use App\Models\Customer;
use App\Models\WorkCategory;
use App\Models\WorkCategoryStatus;
use App\Models\WorkOrder;
use App\Services\Notifications\NotificationService;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class WorkOrderResource extends Resource
{
    protected static ?string $model = WorkOrder::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationGroup = 'Work Management';

    protected static ?int $navigationSort = 10;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Tabs::make('Work Order')
                    ->tabs([
                        Tab::make('Basic Information')
                            ->schema([
                                Section::make('Work Order Information')
                                    ->schema([
                                        Grid::make(2)
                                            ->schema([
                                                TextInput::make('title')
                                                    ->required()
                                                    ->maxLength(255),

                                                Select::make('work_category_id')
                                                    ->label('Category')
                                                    ->options(function () {
                                                        return WorkCategory::where('organization_id', Filament::getTenant()->id)
                                                            ->where('is_active', true)
                                                            ->pluck('name', 'id');
                                                    })
                                                    ->required()
                                                    ->reactive()
                                                    ->afterStateUpdated(fn (callable $set) => $set('current_status_id', null)),

                                                Select::make('current_status_id')
                                                    ->label('Status')
                                                    ->options(function (callable $get) {
                                                        $categoryId = $get('work_category_id');
                                                        if (!$categoryId) {
                                                            return [];
                                                        }

                                                        return WorkCategoryStatus::where('work_category_id', $categoryId)
                                                            ->where('is_active', true)
                                                            ->orderBy('order')
                                                            ->pluck('name', 'id');
                                                    })
                                                    ->required()
                                                    ->disabled(fn (callable $get) => !$get('work_category_id')),

                                                DateTimePicker::make('estimated_completion_date')
                                                    ->label('Estimated Completion'),

                                                Select::make('notification_channel')
                                                    ->options(app(NotificationService::class)->getChannelOptions())
                                                    ->default('email')
                                                    ->required(),
                                            ]),

                                        Textarea::make('description')
                                            ->maxLength(65535)
                                            ->columnSpan('full'),
                                    ]),
                            ]),

                        Tab::make('Customer Information')
                            ->schema([
                                Section::make('Customer Information')
                                    ->schema([
                                        Grid::make(2)
                                            ->schema([
                                                Select::make('customer_id')
                                                    ->label('Existing Customer')
                                                    ->options(function () {
                                                        return Customer::where('organization_id', Filament::getTenant()->id)
                                                            ->pluck('name', 'id');
                                                    })
                                                    ->searchable()
                                                    ->reactive()
                                                    ->afterStateUpdated(function (callable $set, callable $get, $state) {
                                                        if ($state) {
                                                            $customer = Customer::find($state);
                                                            if ($customer) {
                                                                $set('customer_name', $customer->name);
                                                                $set('customer_email', $customer->email);
                                                                $set('customer_phone', $customer->phone);
                                                            }
                                                        }
                                                    }),

                                                Placeholder::make('or_create_new')
                                                    ->label('OR Create New')
                                                    ->content('Fill in the details below to create a new customer'),

                                                TextInput::make('customer_name')
                                                    ->label('Name')
                                                    ->required(fn (Get $get) => !$get('customer_id'))
                                                    ->maxLength(255),

                                                TextInput::make('customer_email')
                                                    ->label('Email')
                                                    ->email()
                                                    ->maxLength(255),

                                                TextInput::make('customer_phone')
                                                    ->label('Phone')
                                                    ->tel()
                                                    ->maxLength(20),
                                            ]),
                                    ]),
                            ]),

                        Tab::make('Additional Details')
                            ->schema([
                                Section::make('Work Order Details')
                                    ->schema([
                                        // Dynamic form for work order details
                                        // This would be enhanced for specific work categories
                                        TextInput::make('details.model')
                                            ->label('Model/Make')
                                            ->helperText('e.g., iPhone 12, Samsung TV')
                                            ->maxLength(255),

                                        TextInput::make('details.serial_number')
                                            ->label('Serial Number')
                                            ->maxLength(255),

                                        Textarea::make('details.condition')
                                            ->label('Condition Description')
                                            ->maxLength(1000),

                                        TextInput::make('details.accessories')
                                            ->label('Included Accessories')
                                            ->helperText('e.g., Charger, Case')
                                            ->maxLength(255),
                                    ]),
                            ]),

                        Tab::make('Notes')
                            ->schema([
                                Section::make('Work Order Notes')
                                    ->schema([
                                        Textarea::make('notes')
                                            ->label('Initial Notes')
                                            ->helperText('These notes will be visible to the customer')
                                            ->maxLength(65535),
                                    ]),
                            ]),
                    ])
                    ->columnSpan('full'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('order_number')
                    ->label('Order #')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->limit(30),

                TextColumn::make('customer.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('workCategory.name')
                    ->label('Category')
                    ->sortable(),

                TextColumn::make('currentStatus.name')
                    ->label('Status')
                    ->sortable()
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Completed' => 'success',
                        'In Progress' => 'warning',
                        'On Hold' => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable(),

                TextColumn::make('estimated_completion_date')
                    ->label('Est. Completion')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Action::make('track')
                    ->label('Track')
                    ->icon('heroicon-o-qr-code')
                    ->url(fn (WorkOrder $record) => $record->getTrackingUrl())
                    ->openUrlInNewTab(),
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                //
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\HistoryRelationManager::class,
            RelationManagers\DetailsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWorkOrders::route('/'),
            'create' => Pages\CreateWorkOrder::route('/create'),
            'view' => Pages\ViewWorkOrder::route('/{record}'),
            'edit' => Pages\EditWorkOrder::route('/{record}/edit'),
        ];
    }
}
