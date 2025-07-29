<?php

namespace App\Filament\Admin\Resources\OrganizationResource\RelationManagers;

use App\Enums\Stripe\Refunds\RefundSubscriptionEnum;
use App\Enums\Stripe\{ProductCurrencyEnum, SubscriptionStatusEnum};
use App\Services\Stripe\Refund\CreateRefundService;
use App\Services\Stripe\Subscription\CancelSubscriptionService;
use Carbon\Carbon;
use Filament\Forms\Components\{Fieldset, Select, TextInput};
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Actions\{Action, ActionGroup};
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Leandrocfe\FilamentPtbrFormFields\Money;

class SubscriptionRelationManager extends RelationManager
{
    protected static string $relationship = 'subscriptions';

    protected static ?string $modelLabel = 'Subscription';

    protected static ?string $modelLabelPlural = "Subscriptions";

    protected static ?string $title = 'Subscription';

    public function table(Table $table): Table
    {
        return $table

            ->columns([

                TextColumn::make('stripe_status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn ($state) => SubscriptionStatusEnum::from($state)->getLabel())
                    ->color(fn ($state) => SubscriptionStatusEnum::from($state)->getColor())
                    ->alignCenter()
                    ->sortable()
                    ->searchable(),

                TextColumn::make('stripe_id')
                    ->label('Id Subscription'),

                TextColumn::make('stripe_period')
                    ->label('Plan Type')
                    ->getStateUsing(function ($record) {
                        // Acessa o preço relacionado via o relacionamento definido
                        return $record->price->interval;
                    })
                    ->alignCenter()
                    ->sortable(),

                TextColumn::make('stripe_price')
                    ->label('Plan Value')
                    ->getStateUsing(function ($record) {
                        // Acessa o preço relacionado via o relacionamento definido
                        return $record->price->unit_amount;
                    })
                    ->money('eur')
                    ->alignCenter()
                    ->sortable(),

                TextColumn::make('ends_at')
                    ->label('Expires At')
                    ->alignCenter()
                    ->dateTime('d/m/Y H:m:s'),

                TextColumn::make('remaining_time')
                    ->label('Remaining Time')
                    ->getStateUsing(function ($record) {
                        $endsAt = $record->ends_at ? Carbon::parse($record->ends_at) : null;

                        if (!$endsAt) {
                            return 'No date set';
                        }

                        $now = now();

                        // Verifica se o plano já expirou
                        if ($now > $endsAt) {
                            return 'Expired';
                        }

                        // Calcula a diferença total em dias e horas
                        $remainingDays  = $now->diffInDays($endsAt, false);
                        $remainingHours = $now->diffInHours($endsAt) % 24;

                        return sprintf('%d days and %02d hours', $remainingDays, $remainingHours);
                    })
                    ->alignCenter(),

            ])
            ->filters([
                //
            ])
            ->headerActions([])
            ->actions([
                ActionGroup::make([
                    Action::make('Cancel Subscription')
                        ->requiresConfirmation()
                        ->action(function (Action $action, $record, array $data) {
                            $cancellationService = new CancelSubscriptionService();
                            $cancellationService->cancel($record, $data);
                        })
                        ->color('danger')
                        ->icon('heroicon-o-key'),

                    Action::make('Generate Refund')
                        ->requiresConfirmation()
                        ->form([

                            Fieldset::make('Plan Data')
                                ->schema([
                                    TextInput::make('stripe_period')
                                        ->label('Plan Type')
                                        ->readOnly()
                                        ->default(function ($record) {
                                            return $record->price->interval;
                                        }),

                                    TextInput::make('stripe_price')
                                        ->label('Plan Value')
                                        ->readOnly()
                                        ->default(function ($record) {
                                            $price = $record->price ? $record->price->unit_amount : 0;

                                            return 'R$ ' . number_format($price, 2, ',', '.');  // Exemplo: R$ 599,99
                                        }),
                                ])->columns(2),

                            Fieldset::make('Values')
                                ->schema([

                                    Money::make('amount')
                                        ->label('Refund Amount')
                                        ->default('100,00')
                                        ->required()
                                        ->rule(function ($get) {

                                            $stripePrice = $get('stripe_price') ? filter_var($get('stripe_price'), FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) : 0;

                                            return "lte:{$stripePrice}";
                                        })
                                        ->validationAttribute('amount')
                                        ->validationMessages([
                                            'lte' => 'The amount cannot be greater than the plan value.',
                                        ]),

                                    Select::make('currency')
                                        ->label('Currency')
                                        ->options(ProductCurrencyEnum::class)
                                        ->required(),

                                ])->columns(2),

                            Fieldset::make('Cancellation Reason')
                                ->schema([
                                    Select::make('reason')
                                        ->label('Select the Reason')
                                        ->options(RefundSubscriptionEnum::class)
                                        ->required(),
                                ])->columns(1),

                            Fieldset::make('Payment ID')
                                ->schema([
                                    TextInput::make('payment_intent')
                                        ->label('Payment ID')
                                        ->readOnly()
                                        ->default(function ($record) {
                                            return $record->payment_intent;
                                        }),
                                ])->columns(1),
                        ])

                        ->requiresConfirmation()
                        ->modalHeading('Generate Refund')
                        ->modalDescription()
                        ->slideOver()
                        ->color('warning')
                        ->icon('fas-hand-holding-dollar')
                        ->action(function (Action $action, $record, array $data) {

                            try {
                                //$refundService = new CreateRefundService();
                                //$refundService->processRefund($record->id, $data);

                                Notification::make()
                                    ->title('Refund Generated')
                                    ->body('Refund generated successfully')
                                    ->success()
                                    ->send();
                            } catch (\Exception $e) {

                                Notification::make()
                                    ->title('Error Creating Price')
                                    ->body('An error occurred while generating the refund in Stripe: ' . $e->getMessage())
                                    ->danger()
                                    ->send();
                            }
                        }),

                    Action::make('Download Invoice')
                        ->label('Download Invoice')
                        ->icon('heroicon-o-document-arrow-down')
                        ->url(fn ($record) => $record->invoice_pdf)
                        ->tooltip('Download Invoice PDF')
                        ->color('primary'),

                ])
                ->icon('fas-sliders')
                ->color('warning'),
            ])

            ->bulkActions([]);
    }
}
