<?php

namespace App\Filament\Resources\WorkOrderResource\RelationManagers;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class HistoryRelationManager extends RelationManager
{
    protected static string $relationship = 'history';

    protected static ?string $recordTitleAttribute = 'notes';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Textarea::make('notes')
                    ->required()
                    ->maxLength(65535),
                Toggle::make('is_public')
                    ->label('Visible to Customer')
                    ->default(true),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('status.name')
                    ->label('Status')
                    ->sortable()
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Completed' => 'success',
                        'In Progress' => 'warning',
                        'On Hold' => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('notes')
                    ->limit(50),

                IconColumn::make('is_public')
                    ->label('Public')
                    ->boolean(),

                TextColumn::make('createdBy.name')
                    ->label('Created By'),

                TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Add Note')
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['status_id'] = $this->ownerRecord->current_status_id;
                        $data['created_by_id'] = auth()->id();
                        return $data;
                    }),
            ])
            ->actions([
                //
            ])
            ->bulkActions([
                //
            ])
            ->defaultSort('created_at', 'desc');
    }
}
