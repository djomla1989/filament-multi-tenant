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
                    Action::make('Cancelar Assinatura')
                        ->requiresConfirmation()
                        ->action(function (Action $action, $record, array $data) {
                            $cancellationService = new CancelSubscriptionService();
                            $cancellationService->cancel($record, $data);
                        })
                        ->color('danger')
                        ->icon('heroicon-o-key'),

                    Action::make('Gerar Reembolso')
                        ->requiresConfirmation()
                        ->form([

                            Fieldset::make('Dados do Plano')
                                ->schema([
                                    TextInput::make('stripe_period')
                                        ->label('Tipo do Plano')
                                        ->readOnly()
                                        ->default(function ($record) {
                                            return $record->price->interval;
                                        }),

                                    TextInput::make('stripe_price')
                                        ->label('Valor do Plano')
                                        ->readOnly()
                                        ->default(function ($record) {
                                            $price = $record->price ? $record->price->unit_amount : 0;

                                            return 'R$ ' . number_format($price, 2, ',', '.');  // Exemplo: R$ 599,99
                                        }),
                                ])->columns(2),

                            Fieldset::make('Valores')
                                ->schema([

                                    Money::make('amount')
                                        ->label('Devolver')
                                        ->default('100,00')
                                        ->required()
                                        ->rule(function ($get) {

                                            $stripePrice = $get('stripe_price') ? filter_var($get('stripe_price'), FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) : 0;

                                            return "lte:{$stripePrice}";
                                        })
                                        ->validationAttribute('amount')
                                        ->validationMessages([
                                            'lte' => 'O valor não pode ser maior que o valor do plano.',
                                        ]),

                                    Select::make('currency')
                                        ->label('Moeda')
                                        ->options(ProductCurrencyEnum::class)
                                        ->required(),

                                ])->columns(2),

                            Fieldset::make('Motivo do Cancelamento')
                                ->schema([
                                    Select::make('reason')
                                        ->label('Selecione o Motivo')
                                        ->options(RefundSubscriptionEnum::class)
                                        ->required(),
                                ])->columns(1),

                            Fieldset::make('Id Pagamento')
                                ->schema([
                                    TextInput::make('payment_intent')
                                        ->label('Id Pagamento')
                                        ->readOnly()
                                        ->default(function ($record) {
                                            return $record->payment_intent;
                                        }),
                                ])->columns(1),
                        ])

                        ->requiresConfirmation()
                        ->modalHeading('Gerar Reembolso')
                        ->modalDescription()
                        ->slideOver()
                        ->color('warning')
                        ->icon('fas-hand-holding-dollar')
                        ->action(function (Action $action, $record, array $data) {

                            try {
                                //$refundService = new CreateRefundService();
                                //$refundService->processRefund($record->id, $data);

                                Notification::make()
                                    ->title('Reembolso Gerado')
                                    ->body('Reembolso gerado com Sucesso')
                                    ->success()
                                    ->send();
                            } catch (\Exception $e) {

                                Notification::make()
                                    ->title('Erro ao Criar Preço')
                                    ->body('Ocorreu um erro ao gerar reembolso na Stripe: ' . $e->getMessage())
                                    ->danger()
                                    ->send();
                            }
                        }),

                    Action::make('Baixar Invoice')
                        ->label('Baixar Invoice')
                        ->icon('heroicon-o-document-arrow-down')
                        ->url(fn ($record) => $record->invoice_pdf)
                        ->tooltip('Baixar PDF da Fatura')
                        ->color('primary'),

                ])
                ->icon('fas-sliders')
                ->color('warning'),
            ])

            ->bulkActions([]);
    }
}
