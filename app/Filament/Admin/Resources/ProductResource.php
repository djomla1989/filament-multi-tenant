<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\ProductResource\RelationManagers\{PricesRelationManager, ProductfeaturesRelationManager};
use App\Filament\Admin\Resources\ProductResource\{Pages};
use App\Models\Product;
use App\Services\Stripe\Product\DeleteStripeProductService;
use Filament\Forms\Components\{Fieldset, FileUpload, TextInput};
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables\Actions\{Action, ActionGroup, DeleteAction, EditAction, ViewAction};
use Filament\Tables\Columns\{TextColumn, ToggleColumn};

use Filament\Tables\Table;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'fas-hand-holding-dollar';

    protected static ?string $navigationGroup = 'Plans';

    protected static ?string $navigationLabel = 'Plans';

    protected static ?string $modelLabel = 'Plan';

    protected static ?string $modelLabelPlural = "Plans";

    protected static ?int $navigationSort = 1;

    protected static bool $isScopedToTenant = false;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                Fieldset::make('Label')
                    ->schema([
                        TextInput::make('stripe_id')
                            ->label('Stripe Plan ID')
                            ->readOnly(),

                        TextInput::make('name')
                            ->label('Plan Name')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('description')
                            ->label('Plan Description')
                            ->required()
                            ->maxLength(255),

                    ])->columns(3),

                Fieldset::make('Plan Image')
                ->schema([
                    FileUpload::make('image')
                        ->label('Plan Image')
                        ->image()
                        ->imageEditor()
                        ->columnSpanFull(),
                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                TextColumn::make('stripe_id')
                    ->label('Stripe Plan ID')
                ->searchable(),

                TextColumn::make('description')
                    ->label('Plan Description')
                    ->searchable(),

                TextColumn::make('name')
                    ->label('Plan Name')
                    ->searchable(),

                TextColumn::make('prices_count')
                    ->label('Registered Prices')
                    ->alignCenter()
                    ->sortable()
                    ->getStateUsing(fn ($record) => (string) $record->prices()->count()),

                TextColumn::make('features_count')
                    ->label('Features')
                    ->alignCenter()
                    ->getStateUsing(fn ($record) => (string) $record->product_features()->where('is_active', true)->count()),

                ToggleColumn::make('is_active')
                    ->label('Active')
                    ->alignCenter(),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                ActionGroup::make([
                    ViewAction::make()
                        ->color('primary'),
                    EditAction::make()
                        ->color('secondary'),
                    DeleteAction::make() ->action(function (Action $action, $record) {
                        try {
                            $deleteStripeProductService = new DeleteStripeProductService();
                            $deleteStripeProductService->execute($record);

                            Notification::make()
                                ->title('Product Deleted')
                                ->body('Product deleted successfully!')
                                ->success()
                                ->send();

                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Erro ao Excluir')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
                ])
                ->icon('fas-sliders')
                ->color('warning'),

            ])

            ->bulkActions([

            ]);
    }

    public static function getRelations(): array
    {
        return [
            PricesRelationManager::class,
            ProductfeaturesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit'   => Pages\EditProduct::route('/{record}/edit'),
        ];
    }

}
