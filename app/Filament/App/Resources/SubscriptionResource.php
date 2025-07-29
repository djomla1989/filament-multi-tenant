<?php

namespace App\Filament\App\Resources;

use App\Enums\Stripe\{CancelSubscriptionEnum, SubscriptionStatusEnum};
use App\Filament\App\Resources\SubscriptionResource\{Pages};
use App\Models\{Subscription};
use App\Services\Stripe\Subscription\CancelSubscriptionService;
use Carbon\Carbon;
use Filament\Forms\Components\{Fieldset, Select, Textarea};
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables\Actions\{Action, ActionGroup};
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use IbrahimBougaoua\FilamentRatingStar\Forms\Components\RatingStar;

class SubscriptionResource extends Resource
{
    protected static ?string $model = Subscription::class;

    protected static ?string $navigationIcon = 'fas-hand-holding-dollar';

    protected static ?string $navigationGroup = 'Administration';

    protected static ?string $navigationLabel = 'My Subscriptions';

    protected static ?string $modelLabel = 'My Subscription';

    protected static ?string $modelLabelPlural = "My Subscriptions";

    protected static ?int $navigationSort = 1;

    protected static bool $isScopedToTenant = true;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([]);
    }

    public static function table(Table $table): Table
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

                TextColumn::make('stripe_period')
                    ->label('Plan Type')
                    ->getStateUsing(function ($record) {
                        // Acessa o preço relacionado via o relacionamento definido
                        return $record->price->interval;
                    })
                    ->badge()
                    ->alignCenter()
                    ->sortable(),

                TextColumn::make('stripe_price')
                    ->label('Plan Value')
                    ->getStateUsing(function ($record) {
                        // Acessa o preço relacionado via o relacionamento definido
                        return $record->price->unit_amount;
                    })
                    ->money('brl')
                    ->alignCenter()
                    ->sortable(),

                TextColumn::make('trial_ends_at')
                    ->label('Trial Period End')
                    ->alignCenter()
                    ->dateTime('d/m/Y'),

                TextColumn::make('current_period_start')
                    ->label('Billing Start')
                    ->alignCenter()
                    ->dateTime('d/m/Y'),

                TextColumn::make('ends_at')
                    ->label('Expires On')
                    ->alignCenter()
                    ->dateTime('d/m/Y'),

                TextColumn::make('remaining_time')
                    ->label('Remaining Time')

                    ->getStateUsing(function ($record) {
                        $endsAt = $record->ends_at ? Carbon::parse($record->ends_at) : null;

                        if (!$endsAt) {
                            return 'No date defined';
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
            ->actions([
                ActionGroup::make([
                                            Action::make('Cancel Subscription')
                        ->form([

                            Fieldset::make('Cancellation Reason')
                                ->schema([
                                    Select::make('reason')
                                        ->label('Select Reason')
                                        ->options(CancelSubscriptionEnum::class)
                                        ->required(),
                                ])->columns(1),

                            Fieldset::make('Your Impressions')
                                ->schema([
                                    Textarea::make('coments')
                                        ->label('Comments or Feedback')
                                        ->rows(4)
                                        ->columnSpan('full'),
                                ])->columns(1),

                            Fieldset::make('Your Rating')
                                ->schema([
                                    RatingStar::make('rating')
                                        ->label('Rating')
                                        ->required()
                                        ->columnSpan('full'),
                                ])->columns(1),

                        ])
                        ->requiresConfirmation()
                        ->modalHeading('Confirm Cancellation')
                        ->modalDescription(function ($record) {
                            // Usando Carbon para formatar a data ends_at
                            $endsAt = Carbon::parse($record->ends_at)->format('d/m/Y H:i'); // Formato desejado

                            return "Warning!!! After cancellation, you will have access to the platform until: {$endsAt}, after that date no charges will be made, your access will be revoked and all data will be deleted. Do you want to continue?";
                        })
                        ->slideOver()
                        ->slideOver()
                        ->action(function (Action $action, $record, array $data) {
                            try {

                                $cancellationService = new CancelSubscriptionService();
                                $cancellationService->cancel($record, $data);

                            } catch (\Exception $e) {

                                Notification::make()
                                    ->title('Erro ao Criar Preço')
                                    ->body('Ocorreu um erro ao criar o preço no Stripe: ' . $e->getMessage())
                                    ->danger()
                                    ->send();
                            }
                        })

                        ->color('danger')
                        ->icon('heroicon-o-key'),

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
            'index' => Pages\ListSubscriptions::route('/'),
            //'create' => Pages\CreateSubscription::route('/create'),
            //'view' => Pages\ViewSubscription::route('/{record}'),
            //'edit'   => Pages\EditSubscription::route('/{record}/edit'),
        ];
    }
    public static function canCreate(): bool
    {
        return false;
    }
}
