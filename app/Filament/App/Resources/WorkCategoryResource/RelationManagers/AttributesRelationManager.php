<?php

namespace App\Filament\App\Resources\WorkCategoryResource\RelationManagers;

use App\Models\WorkCategoryAttribute;
use Filament\Facades\Filament;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AttributesRelationManager extends RelationManager
{
    protected static string $relationship = 'attributes';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $title = 'Attribute';

    protected static ?string $modelLabel = 'Category Attribute';

    protected static ?string $pluralModelLabel = 'Category Attributes';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(2)
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255),

                        Select::make('type')
                            ->options(WorkCategoryAttribute::getTypes())
                            ->required()
                            ->reactive(),

                        Textarea::make('description')
                            ->columnSpan(2),

                        Checkbox::make('is_required')
                            ->label('Required')
                            ->default(false),

                        Checkbox::make('is_active')
                            ->label('Active')
                            ->default(true),
                    ]),

                Repeater::make('options')
                    ->schema([
                        TextInput::make('label')
                            ->required(),
                        TextInput::make('value')
                            ->required(),
                    ])
                    ->columns(2)
                    ->itemLabel(fn (array $state): ?string => $state['label'] ?? null)
                    ->visible(fn (callable $get) => in_array($get('type'), ['select', 'radio']))
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->reorderable('order')
            ->defaultSort('order')
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('type')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('description')
                    ->limit(50)
                    ->searchable(),

                IconColumn::make('is_required')
                    ->label('Required')
                    ->boolean()
                    ->sortable(),

                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([])
            ->headerActions([
                CreateAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['organization_id'] = Filament::getTenant()->id;
                        return $data;
                    }),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                DeleteBulkAction::make(),
            ]);
    }
}
