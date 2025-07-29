<?php

namespace App\Filament\Admin\Resources;

use App\Enums\Stripe\PromotionDurationEnum;
use App\Filament\Admin\Resources\PromotionCodeResource\{Pages};
use App\Models\{Organization, PromotionCode};
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\{DateTimePicker, Fieldset, Select, TextInput, Toggle};
use Filament\Forms\{Form, Set};
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\{Tables};
use Illuminate\Support\Str;

class PromotionCodeResource extends Resource
{
    protected static ?string $model = PromotionCode::class;

    protected static ?string $navigationIcon = 'fas-comment-dollar';

    protected static ?string $navigationGroup = 'Plans';

    protected static ?string $navigationLabel = 'Promotional Code';

    protected static ?string $modelLabel = 'Code';

    protected static ?string $modelLabelPlural = "Promotional Codes";

    protected static ?int $navigationSort = 3;

    protected static bool $isScopedToTenant = false;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                                    Fieldset::make('Promotional Code')
                    ->schema([

                        TextInput::make('code')
                             ->label('Code')
                             ->required()
                             ->suffixAction(
                                 Action::make('codeGenerator')
                                     ->icon('fas-gear')
                                     ->action(function (Set $set, $state) {
                                         $randomCode = Str::random(10);
                                         $set('code', $randomCode);
                                     })
                             ),

                        DateTimePicker::make('expires_at')
                            ->label('Expiration Date')
                            ->displayFormat('d/m/Y H:i:s')
                            ->required(),

                        DateTimePicker::make('redeem_by')
                            ->label('Redemption Deadline')
                            ->displayFormat('d/m/Y H:i:s')
                            ->rule('after_or_equal:expires_at')
                            ->validationAttribute('redeem_by')
                            ->validationMessages([
                                'after_or_equal' => 'Redemption date is earlier than the coupon expiration date',
                            ])
                            ->required(),

                        TextInput::make('max_redemptions')
                            ->label('Number of Codes')
                            ->numeric(),

                    ])->columns(4),

                Fieldset::make('Label')
                ->schema([

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

                Fieldset::make('Label')
                ->schema([

                    Toggle::make('valid')
                        ->label('Do you want to activate the Coupon?')
                        ->default(true)
                        ->onColor('success')
                        ->offColor('info')
                        ->required(),

                    Toggle::make('first_time_transaction')
                        ->label('Valid only for First Transaction?')
                        ->default(false)
                        ->onColor('success')
                        ->offColor('info')
                        ->required(),

                    Toggle::make('customer_optional')
                        ->label('Coupon valid only for one customer?')
                        ->onColor('success')
                        ->offColor('info')
                        ->reactive()
                        ->default(false),

                ])->columns(3),

                Fieldset::make('Customer')
                ->hidden(fn ($get) => $get('customer_optional') === false)
                ->schema([

                    Select::make('customer')
                        ->reactive()
                        ->requiredUnless('customer_optional', true)
                        ->label('Select the Customer who will receive the Coupon')
                        ->options(function () {
                            return Organization::all()->pluck('name', 'stripe_id');
                        }),

                ])->columns(3),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id_promotional_code')
                    ->searchable(),
                Tables\Columns\IconColumn::make('active')
                    ->boolean(),
                Tables\Columns\TextColumn::make('code')
                    ->searchable(),
                Tables\Columns\TextColumn::make('id_cupom_code')
                    ->searchable(),
                Tables\Columns\TextColumn::make('duration')
                    ->searchable(),
                Tables\Columns\TextColumn::make('duration_in_months')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('percent_off')
                    ->searchable(),
                Tables\Columns\TextColumn::make('max_redemptions')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('redeem_by')
                    ->searchable(),
                Tables\Columns\TextColumn::make('customer')
                    ->searchable(),
                Tables\Columns\IconColumn::make('valid')
                    ->boolean(),
                Tables\Columns\IconColumn::make('first_time_transaction')
                    ->boolean(),
                Tables\Columns\TextColumn::make('expires_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),

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
            'index'  => Pages\ListPromotionCodes::route('/'),
            'create' => Pages\CreatePromotionCode::route('/create'),
            'view'   => Pages\ViewPromotionCode::route('/{record}'),
            'edit'   => Pages\EditPromotionCode::route('/{record}/edit'),
        ];
    }
}
