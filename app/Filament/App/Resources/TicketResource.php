<?php

namespace App\Filament\App\Resources;

use App\Enums\TenantSuport\{TicketPriorityEnum, TicketTypeEnum};
use App\Filament\App\Resources\TicketResource\RelationManagers\TicketResponsesRelationManager;
use App\Filament\App\Resources\TicketResource\{Pages};
use App\Models\Ticket;
use Carbon\Carbon;
use Filament\Forms\Components\{Fieldset, FileUpload, RichEditor, Select, TextInput};
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Filament\{Tables};
use Illuminate\Database\Eloquent\{Model};

class TicketResource extends Resource
{
    protected static ?string $model = Ticket::class;

    protected static ?string $navigationIcon = 'fas-bullhorn';

    protected static ?string $navigationGroup = 'Suporte';

    protected static ?string $navigationLabel = 'Solicitações';

    protected static ?string $modelLabel = 'Ticket';

    protected static ?string $modelLabelPlural = "Tickets";

    protected static ?int $navigationSort = 1;

    protected static bool $isScopedToTenant = true;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Fieldset::make('Classification')
                    ->schema([
                        TextInput::make('title')
                            ->label('Subject')
                            ->required()
                            ->maxLength(50),

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
                            ->label('Description')
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

                        return "{$diff->d} days, {$diff->h} hours";

                    })
                    ->alignCenter()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Created at')
                    ->dateTime('d/m/Y H:m:s')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Updated at')
                    ->dateTime('d/m/Y H:m:s')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

            ])
            ->filters([])
            ->groups([
                Group::make('user.name')
                    ->label('User'),
                Group::make('status')
                    ->label('Status'),
                Group::make('type')
                    ->label('Type'),
            ])

            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
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
