<?php

namespace App\Filament\Admin\Resources\OrganizationResource\RelationManagers;

use App\Models\WhatsappInstance;
use App\Services\Evolution\Instance\{ConnectEvolutionInstanceService, DeleteEvolutionInstanceService, FetchEvolutionInstanceService, LogOutEvolutionInstanceService, RestartEvolutionInstanceService};
use App\Services\Evolution\Message\SendMessageEvolutionService;
use Filament\Facades\Filament;
use Filament\Forms\Components\{Fieldset, Section, TextInput, ToggleButtons};
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Actions\{Action, ActionGroup, DeleteAction, EditAction, ViewAction};
use Filament\Tables\Columns\{ImageColumn, TextColumn};
use Filament\Tables\Table;
use Filament\{Tables};
use Leandrocfe\FilamentPtbrFormFields\PhoneNumber;

class WhatsappInstanceRelationManager extends RelationManager
{
    protected static string $relationship = 'whatsappInstances';

    protected static ?string $modelLabel = 'WhatsApp';

    protected static ?string $modelLabelPlural = "WhatsApp";

    protected static ?string $title = 'WhatsApp';

    public function form(Form $form): Form
    {
        return $form
           ->schema([
               Section::make('Instance Data')
                   ->schema([

                       TextInput::make('name')
                           ->label('Instance Name')
                           ->unique(WhatsappInstance::class, 'name', ignoreRecord: true)
                           ->default(fn () => Filament::getTenant()?->slug ?? '')
                           ->required()
                           ->prefixIcon('fas-id-card')
                           ->validationMessages([
                               'unique' => 'Name of the instance already registered.',
                           ])
                           ->maxLength(20),

                       PhoneNumber::make('number')
                           ->label('WhatsApp Number')
                           ->unique(WhatsappInstance::class, 'number', ignoreRecord: true)
                           ->mask('+55 (99) 99999-9999')
                           ->placeholder('+55 (99) 99999-9999')
                           ->required()
                           ->prefixIcon('fab-whatsapp')
                           ->validationMessages([
                               'unique' => 'Number already registered.',
                           ]),

                   ])->columns(2),

                                  Section::make('Instance Settings')
                   ->schema([
                       ToggleButtons::make('groups_ignore')
                           ->label('Ignore Groups')
                           ->inline()
                           ->boolean()
                           ->required(),

                       ToggleButtons::make('always_online')
                           ->label('Always Online')
                           ->inline()
                           ->boolean()
                           ->required(),

                       ToggleButtons::make('read_messages')
                           ->label('Mark Messages as Read')
                           ->inline()
                           ->boolean()
                           ->required(),

                       ToggleButtons::make('read_status')
                           ->label('Mark Status as Read')
                           ->inline()
                           ->boolean()
                           ->required(),

                       ToggleButtons::make('sync_full_history')
                           ->label('Sync History')
                           ->inline()
                           ->boolean()
                           ->required(),

                       ToggleButtons::make('reject_call')
                           ->label('Reject Calls')
                           ->inline()
                           ->boolean()
                           ->live()
                           ->reactive()
                           ->required(),

                       TextInput::make('msg_call')
                           ->label('Message for Rejected Calls')
                           ->required()
                           ->hidden(fn ($get) => $get('reject_call') == false)
                           ->maxLength(255),

                   ])->columns(4),
           ]);
    }

    public function table(Table $table): Table
    {
        return $table
              ->columns([
                  ImageColumn::make('profile_picture_url')
                      ->label('Profile Picture')
                      ->alignCenter()
                      ->circular()
                      ->getStateUsing(fn ($record) => $record->profile_picture_url ?: 'https://www.cidademarketing.com.br/marketing/wp-content/uploads/2018/12/whatsapp-640x640.png'),

                  TextColumn::make('status')
                      ->label('Status')
                      ->alignCenter()
                      ->badge()
                      ->searchable(),

                  TextColumn::make('name')
                      ->label('Instance Name')
                      ->searchable(),

                  TextColumn::make('number')
                      ->label('Number')
                      ->searchable(),

                  TextColumn::make('instance_id')
                      ->label('Instance ID')
                      ->searchable(),

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
                  Action::make('showQr')
                      ->hidden(fn ($record) => $record->status->value === 'open')
                      ->label('QR Code')
                      ->icon('heroicon-o-qr-code')
                      ->color('success')
                      ->modalHeading('Qr Code WhatsApp')
                      ->modalSubmitAction(false)
                      ->modalCancelAction(
                          \Filament\Actions\Action::make('close')
                              ->label('CLOSE')
                              ->color('danger') // Colors: primary, secondary, success, danger, warning, gray
                              ->extraAttributes(['class' => 'w-full']) // Full width
                              ->close()
                      )
                      ->modalWidth('md') // ou sm, lg, xl, 2xl, 3xl, 4xl, 5xl, 6xl, 7xl
                      ->modalContent(fn ($record) => view('evolution.qr-code-modal', [
                          'qrCode' => str_replace('\/', '/', $record->getRawOriginal('qr_code')),
                      ])),

                  ActionGroup::make([
                      Action::make('RestartInstance')
                          ->label('Restart Instance')
                          ->hidden(fn ($record) => $record->status->value === 'close')
                          ->icon('fas-rotate-right')
                          ->color('warning')
                          ->action(function ($record, $livewire) {
                              $service  = new RestartEvolutionInstanceService();
                              $response = $service->restartInstance($record->name);

                              if (isset($response['error'])) {
                                  Notification::make()
                                      ->title('Error restarting')
                                      ->danger()
                                      ->send();
                              } else {
                                  Notification::make()
                                      ->title('Instance restarted')
                                      ->success()
                                      ->send();
                              }
                              $livewire->dispatch('refresh');
                          }),

                      Action::make('LogoutInstance')
                          ->hidden(fn ($record) => $record->status->value !== 'open')
                          ->label('Disconnect Instance')
                          ->icon('fas-sign-out-alt')
                          ->color('danger')
                          ->action(function ($record, $livewire) {
                              $service  = new LogOutEvolutionInstanceService();
                              $response = $service->logoutInstance($record->name);

                              if (!empty($response['error'])) {
                                  Notification::make()
                                      ->title('Error disconnecting')
                                      ->danger()
                                      ->send();
                              } else {
                                  Notification::make()
                                      ->title('Instance disconnected')
                                      ->body('Log in again and scan the QR Code')
                                      ->success()
                                      ->send();
                              }
                              $livewire->dispatch('refresh');
                          }),

                      Action::make('ConectInstance')
                          ->hidden(fn ($record) => $record->status->value === 'open')
                          ->label('Connect Instance')
                          ->icon('fas-sign-in-alt')
                          ->color('info')
                          ->action(function ($record, $livewire) {
                              $service  = new ConnectEvolutionInstanceService();
                              $response = $service->connectInstance($record->name);

                              if (isset($response['error'])) {
                                  Notification::make()
                                      ->title('Error reconnecting')
                                      ->danger()
                                      ->send();
                              } else {
                                  Notification::make()
                                      ->title('Instance reconnected')
                                      ->body('Scan the QR code to activate data synchronization')
                                      ->success()
                                      ->send();
                              }
                              $livewire->dispatch('refresh');
                          }),

                      Action::make('syncInstance')
                          ->label('Sync Data')
                          ->icon('fas-sync')
                          ->color('info')
                          ->action(function ($record, $livewire) {
                              $service  = new FetchEvolutionInstanceService();
                              $response = $service->fetchInstance($record->name);

                              if (isset($response['error'])) {
                                  Notification::make()
                                      ->title('Error synchronizing data')
                                      ->danger()
                                      ->send();
                              } else {
                                  Notification::make()
                                      ->title('Instance synchronized')
                                      ->body('Data synchronized successfully')
                                      ->success()
                                      ->send();
                              }
                              // Fecha o ActionGroup
                              $livewire->dispatch('close-modal');
                              $livewire->dispatch('refresh');
                          }),

                                                Action::make('Send Message')
                          ->requiresConfirmation()
                          ->hidden(fn ($record) => $record->status->value !== 'open')
                          ->form([
                              Fieldset::make('Send your message')
                                  ->schema([
                                      PhoneNumber::make('number_whatsapp')
                                          ->label('WhatsApp Number')
                                          ->mask('+55 (99) 99999-9999')
                                          ->placeholder('+55 (99) 99999-9999')
                                          ->required()
                                          ->prefixIcon('fab-whatsapp'),

                                      TextInput::make('message')
                                          ->label('Message'),

                                  ])->columns(1),
                          ])

                          ->modalHeading('Send Message')
                          ->modalDescription('Send a test message to validate the service')
                          ->color('success')
                          ->icon('fab-whatsapp')
                          ->action(function (Action $action, $record, array $data, $livewire) {
                              try {
                                  $service = new SendMessageEvolutionService();
                                  $service->sendMessage($record->name, $data);

                                  Notification::make()
                                      ->title('Message sent')
                                      ->body('Message sent successfully')
                                      ->success()
                                      ->send();
                              } catch (\Exception $e) {
                                  Notification::make()
                                      ->title('Error sending message')
                                      ->body('An error occurred while sending message: ' . $e->getMessage())
                                      ->danger()
                                      ->send();
                              }
                              $livewire->dispatch('refresh');
                          })
                          ->icon('fab-whatsapp')
                          ->color('success'),
                  ])
                      ->icon('fab-whatsapp')
                      ->color('success'),

                  ActionGroup::make([
                      ViewAction::make()
                            ->color('primary'),
                      EditAction::make()
                            ->color('secondary'),
                      DeleteAction::make()
                            ->action(function ($record, $livewire) {
                                $service  = new DeleteEvolutionInstanceService();
                                $response = $service->deleteInstance($record->name);

                                // Deleta o registro local após sucesso na API
                                $record->delete();
                                $livewire->dispatch('refresh');
                            }),
                  ])
                      ->icon('fas-sliders')
                      ->color('warning'),
              ])
              ->bulkActions([
                  Tables\Actions\BulkActionGroup::make([
                      Tables\Actions\DeleteBulkAction::make(),
                  ]),
              ]);
    }
}
