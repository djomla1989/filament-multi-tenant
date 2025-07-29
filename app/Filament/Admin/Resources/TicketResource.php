<?php

namespace App\Filament\Admin\Resources;

use App\Enums\TenantSuport\{TicketPriorityEnum, TicketStatusEnum, TicketTypeEnum};
use App\Filament\Admin\Resources\TicketResource\RelationManagers\TicketResponsesRelationManager;
use App\Filament\Admin\Resources\TicketResource\{Pages};
use App\Models\{Organization, Ticket};
use Carbon\Carbon;
use Filament\Forms\Components\{Fieldset, FileUpload, RichEditor, Select, TextInput};
use Filament\Forms\{Form, Set};
use Filament\Resources\Resource;
use Filament\Tables\Actions\{ActionGroup, DeleteAction, EditAction, ViewAction};
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\{Tables};
use Illuminate\Database\Eloquent\{Model};

class TicketResource extends Resource
{
    protected static ?string $model = Ticket::class;

    protected static ?string $navigationIcon = 'fas-comment-dots';

    protected static ?string $navigationGroup = 'Administration';

    protected static ?string $navigationLabel = 'Tickets';

    protected static ?string $modelLabel = 'Ticket';

    protected static ?string $modelLabelPlural = "Tickets";

    protected static ?int $navigationSort = 2;

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::whereNotIn('status', [
            TicketStatusEnum::CLOSED->value,
            TicketStatusEnum::RESOLVED->value,
        ])->count();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                Fieldset::make('Company')
                    ->schema([
                        TextInput::make('title')
                            ->label('Subject')
                            ->required()
                            ->maxLength(50),

                        Select::make('organization_id')
                            ->label('Company')
                            ->required()
                            ->options(Organization::all()->pluck('name', 'id')) // Exibe todas as organizações
                            ->afterStateUpdated(function (Set $set, $state) {
                                // Limpar o campo de usuário quando a organização for alterada
                                $set('user_id', null);
                            }),

                        Select::make('user_id')
                            ->label('User')
                            ->searchable()   // Permite pesquisa
                            ->preload()      // Carrega os dados de forma antecipada
                            ->live()          // Atualiza as opções em tempo real
                            ->required()
                            ->options(function ($get) {
                                // Obter o ID da organização selecionada
                                $organizationId = $get('organization_id');

                                // Verificar se a organização foi selecionada
                                if ($organizationId) {
                                    // Carregar os membros (usuários) da organização selecionada
                                    $organization = Organization::find($organizationId);

                                    if ($organization) {
                                        // Acessar os membros e retornar um array de opções
                                        return $organization->members->pluck('name', 'id')->toArray(); // Usando pluck para extrair os dados
                                    }
                                }

                                // Se não houver organização selecionada, retornar um array vazio
                                return [];
                            }),
                    ])->columns(3),

                Fieldset::make('Classification')
                    ->schema([
                        Select::make('status')
                            ->label('Status')
                            ->options(TicketStatusEnum::class)
                            ->searchable()
                            ->required(),

                        Select::make('type')
                            ->label('Type')
                            ->options(TicketTypeEnum::class)
                            ->searchable()
                            ->required(),

                        Select::make('priority')
                            ->label('Priority')
                            ->options(TicketPriorityEnum::class)
                            ->searchable()
                            ->required(),

                    ])->columns(3),

                Fieldset::make('Ticket Details')
                    ->schema([
                        RichEditor::make('description')
                            ->label('Details')
                            ->required()
                            ->columnSpanFull(),
                    ]),

                Fieldset::make('Attachments')
                    ->schema([
                        FileUpload::make('file')
                            ->multiple()
                            ->label('Files'),

                        FileUpload::make('image_path')
                            ->label('Images')
                            ->image()
                            ->imageEditor(),
                    ])->columns(2),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('Request')
                    ->alignCenter()
                    ->sortable(),

                TextColumn::make('organization.name')
                    ->label('Tenant')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('user.name')
                    ->label('Requester')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('title')
                    ->label('Subject')
                    ->searchable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->alignCenter()
                    ->badge()
                    ->sortable(),

                TextColumn::make('priority')
                    ->label('Priority')
                    ->alignCenter()
                    ->badge()
                    ->sortable(),

                TextColumn::make('type')
                    ->label('Type')
                    ->alignCenter()
                    ->badge()
                    ->sortable(),

                TextColumn::make('lifetime')
                    ->label('Lifetime')
                    ->getStateUsing(function (Model $record) {
                        $createdAt = Carbon::parse($record->created_at);
                        $closedAt  = $record->closed_at ? Carbon::parse($record->closed_at) : now();
                        $diff      = $createdAt->diff($closedAt);

                        return "{$diff->d} dias, {$diff->h} horas";
                    })
                    ->alignCenter()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Created at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('closed_at')
                    ->label('Closed at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Updated at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                    DeleteAction::make(),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            TicketResponsesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListTickets::route('/'),
            'create' => Pages\CreateTicket::route('/create'),
            'view'   => Pages\ViewTicket::route('/{record}'),
            'edit'   => Pages\EditTicket::route('/{record}/edit'),
        ];
    }
}
