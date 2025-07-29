<?php

namespace App\Filament\Admin\Resources\OrganizationResource\RelationManagers;

use App\Mail\PasswordResetMail;
use App\Models\User;
use Filament\Forms\Components\{Fieldset, TextInput, Toggle};
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Actions\{Action, ActionGroup, DeleteAction, EditAction, ViewAction};
use Filament\Tables\Columns\{ImageColumn, TextColumn, ToggleColumn};
use Filament\Tables\Table;
use Filament\{Tables};
use Illuminate\Support\Facades\{Hash, Mail};
use Illuminate\Support\Str;

class UserRelationManager extends RelationManager
{
    protected static string $relationship = 'users';

    protected static ?string $modelLabel = 'Users';

    protected static ?string $modelLabelPlural = "Users";

    protected static ?string $title = 'Organization Users';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Fieldset::make('User Data')
                    ->schema([
                        TextInput::make('name')
                            ->label('Username')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('email')
                            ->label('E-mail')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                    ])->columns(2),

                Fieldset::make('password')
                    ->visible(fn ($livewire) => $livewire->mountedTableActionRecord === null)
                    ->schema([

                        TextInput::make('password')
                            ->password()
                            ->label('Password')
                            // Exibe apenas ao criar
                            ->required(fn ($livewire) => $livewire->mountedTableActionRecord === null), // Requerido apenas ao criar

                    ])->columns(2),

                Fieldset::make('System')
                    ->schema([
                        Toggle::make('is_admin')
                            ->label('Administrador')
                            ->required(),
                    ])->columns(2),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('user')

            ->columns([

                TextColumn::make('id')
                    ->label('ID')
                    ->alignCenter(),

                ImageColumn::make('avatar_url')
                    ->label('Avatar')
                    ->circular()
                    ->getStateUsing(function ($record) {
                        return $record->getFilamentAvatarUrl();
                    })
                    ->alignCenter(),

                TextColumn::make('name')
                    ->label('Name'),

                TextColumn::make('email')
                    ->label('E-mail'),

                ToggleColumn::make('is_admin')
                    ->alignCenter()
                    ->label('Administrador'),

                TextColumn::make('created_at')
                    ->label('Created at')
                    ->dateTime('d/m/Y H:m:s')
                    ->alignCenter()
                    ->sortable(),

                TextColumn::make('email_verified_at')
                    ->label('Verified at')
                    ->dateTime('d/m/Y H:m:s')
                    ->alignCenter()
                    ->sortable(),

            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['email_verified_at'] = now();

                        return $data;
                    }),
            ])
            ->actions([

                ActionGroup::make([
                    ViewAction::make()
                        ->color('primary'),
                    EditAction::make()
                        ->color('secondary'),
                    DeleteAction::make(),
                    Action::make('Reset Password')
                        ->requiresConfirmation()
                        ->action(function (User $user) {
                            $newPassword = Str::random(8);

                            // Define a nova senha criptografada
                            $user->password = Hash::make($newPassword);
                            $user->save();
                            // Envia o e-mail com a nova senha
                            Mail::to($user->email)->queue(new PasswordResetMail($newPassword, $user->name));

                            Notification::make()
                                ->title('Password Changed Successfully')
                                ->body('An email has been sent to the user with the new password.')
                                ->success()
                                ->send();
                        })
                        ->color('warning') // Defina a cor, como amarelo para chamar atenção
                        ->icon('heroicon-o-key'), // Ícone da chave
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
