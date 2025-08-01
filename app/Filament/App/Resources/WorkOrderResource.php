<?php

namespace App\Filament\App\Resources;

use App\Filament\App\Resources\WorkOrderResource\Pages;
use App\Filament\App\Resources\WorkOrderResource\RelationManagers;
use App\Models\Customer;
use App\Models\WorkCategory;
use App\Models\WorkCategoryStatus;
use App\Models\WorkOrder;
use App\Services\Notifications\NotificationService;
use App\Services\WorkOrder\DynamicAttributesService;
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
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class WorkOrderResource extends Resource
{
    protected static ?string $model = WorkOrder::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationGroup = 'Workspace';

    protected static ?string $navigationLabel = 'Work Orders';

    protected static ?string $modelLabel = 'Work Order';

    protected static ?string $modelLabelPlural = 'Work Orders';

    protected static ?int $navigationSort = 2;

    protected static bool $isScopedToTenant = true;

    protected static ?string $tenantOwnershipRelationshipName = 'organization';

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
                                                    ->afterStateUpdated(function (callable $set, $state) {
                                                        $set('current_status_id', null);
                                                        $set('attribute_values', []);
                                                    }),

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
                                                    ->disabled(fn(callable $get) => !$get('work_category_id')),

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
                                                    ->required(fn(Get $get) => !$get('customer_id'))
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

                        Tab::make('Category Attributes')
                            ->schema(function (callable $get) {
                                $categoryId = $get('work_category_id');

                                if (!$categoryId) {
                                    return [
                                        Placeholder::make('no_category')
                                            ->label('No Category Selected')
                                            ->content('Please select a work category to see its attributes.'),
                                    ];
                                }

                                $attributesService = app(DynamicAttributesService::class);
                                $fields = $attributesService->getDynamicFormFields($categoryId);

                                if (empty($fields)) {
                                    return [
                                        Placeholder::make('no_attributes')
                                            ->label('No Attributes')
                                            ->content('This category has no custom attributes defined.'),
                                    ];
                                }

                                return [
                                    Section::make('Category Custom Attributes')
                                        ->schema($fields),
                                ];
                            })
                            ->visible(fn(callable $get) => (bool)$get('work_category_id')),

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
                    ->color(fn(string $state): string => match ($state) {
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
                ActionGroup::make([
                    Action::make('track')
                        ->label('Track')
                        ->icon('heroicon-o-qr-code')
                        ->url(fn(WorkOrder $record) => $record->getTrackingUrl())
                        ->openUrlInNewTab(),
                    ViewAction::make(),
                    EditAction::make(),
                    DeleteAction::make(),
                ]),
            ])
            ->bulkActions([
                //
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\HistoryRelationManager::class,
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
