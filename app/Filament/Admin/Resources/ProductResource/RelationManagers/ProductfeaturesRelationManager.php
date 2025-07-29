<?php

namespace App\Filament\Admin\Resources\ProductResource\RelationManagers;

use Filament\Forms\Components\{Fieldset, TextInput, Textarea};
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\{TextColumn, ToggleColumn};
use Filament\Tables\Table;
use Filament\{Tables};

class ProductfeaturesRelationManager extends RelationManager
{
    protected static string $relationship = 'product_features';

    protected static ?string $modelLabel = 'Plan Feature';

    protected static ?string $modelLabelPlural = "Plan Features";

    protected static ?string $title = 'Plan Features';

    public function form(Form $form): Form
    {
        return $form
            ->schema([

                Fieldset::make('Feature')
                ->schema([
                    TextInput::make('name')
                    ->label('Feature Name')
                    ->required()
                    ->maxLength(255),
                ])->columns(1),

                Fieldset::make('Feature Description')
                ->schema([
                    Textarea::make('description')
                    ->label('Feature Description')
                    ->required()
                    ->maxLength(255),
                ])->columns(1),

            ]);

    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')
                    ->label('Feature Name')
                    ->searchable(),

                TextColumn::make('description')
                    ->label('Feature Description'),

                ToggleColumn::make('is_active')
                    ->label('Active for Client')
                    ->alignCenter(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
