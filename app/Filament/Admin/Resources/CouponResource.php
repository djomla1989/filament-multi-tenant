<?php

namespace App\Filament\Admin\Resources;

use App\Enums\Stripe\{ProductCurrencyEnum, PromotionDurationEnum};
use App\Filament\Admin\Resources\CouponResource\{Pages};
use App\Models\Coupon;
use App\Services\Stripe\Discount\{DeleteStripeCouponService};
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\{Fieldset, Select, TextInput};
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\{DeleteAction, EditAction, ViewAction};
use Filament\Tables\Columns\{TextColumn};
use Filament\Tables\Table;

class CouponResource extends Resource
{
    protected static ?string $model = Coupon::class;

    protected static ?string $navigationIcon = 'fas-ticket';

    protected static ?string $navigationGroup = 'Planos';

    protected static ?string $navigationLabel = 'Discount Coupon';

    protected static ?string $modelLabel = 'Coupon';

    protected static ?string $modelLabelPlural = "Coupons";

    protected static ?int $navigationSort = 2;

    protected static bool $isScopedToTenant = false;
    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                Fieldset::make('Promotional Code')
                ->schema([

                    TextInput::make('coupon_code')
                        ->label('Code')
                        ->maxLength(255)
                        ->readOnly(),

                    TextInput::make('name')
                        ->label('Coupon name')
                        ->maxLength(20),

                    Select::make('currency')
                        ->label('Currency')
                        ->options(ProductCurrencyEnum::class)
                        ->reactive()
                        ->required(),

                    TextInput::make('percent_off')
                        ->label('Discount Percentage')
                        ->prefixIcon('fas-percent')
                        ->numeric()
                        ->rule('max:100')
                        ->validationAttribute('percent_off')
                        ->validationMessages([
                            'max' => 'The discount cannot be greater than 100%',
                        ])
                        ->required(),

                    TextInput::make('max_redemptions')
                        ->label('Number of Coupons')
                        ->numeric(),

                ])->columns(5),

                Fieldset::make('Promotional Code')
                ->schema([
                    DateTimePicker::make('redeem_by')
                        ->label('Expiration Date')
                        ->displayFormat('d/m/Y H:i:s'),

                    Select::make('duration')
                        ->label('Duration')
                        ->options(PromotionDurationEnum::class)
                        ->reactive()
                        ->required(),

                    TextInput::make('duration_in_months')
                        ->label('Duration in Months')
                        ->hidden(fn ($get) => $get('duration') != 'repeating')
                        ->numeric(),

                ])->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('coupon_code')
                    ->label('Coupon Code')
                    ->alignCenter()
                    ->searchable(),

                TextColumn::make('name')
                    ->label('Coupon name')
                    ->searchable(),

                TextColumn::make('duration')
                    ->label('Duration')
                    ->searchable(),

                TextColumn::make('duration_in_months')
                    ->label('Duration in Months')
                    ->alignCenter()
                    ->numeric()
                    ->sortable(),

                TextColumn::make('percent_off')
                    ->label('Discount Percentage')
                    ->alignCenter()
                    ->searchable(),

                TextColumn::make('max_redemptions')
                    ->label('Number of Coupons')
                    ->alignCenter()
                    ->numeric()
                    ->sortable(),

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
                    DeleteAction::make()
                    ->before(function ($record, $action) {
                        // Chamando o serviço de deleção antes de remover o registro do banco
                        $deleteCouponService = new DeleteStripeCouponService();

                        try {
                            $deleteCouponService->deleteCouponCode($record->coupon_code);

                        } catch (\Exception $e) {
                            $action->notify('danger', 'Erro ao deletar o cupom no Stripe: ' . $e->getMessage());

                            throw new \Exception('Falha na API do Stripe: ' . $e->getMessage());
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListCoupons::route('/'),
            'create' => Pages\CreateCoupon::route('/create'),
            'view'   => Pages\ViewCoupon::route('/{record}'),
            'edit'   => Pages\EditCoupon::route('/{record}/edit'),
        ];
    }
}
