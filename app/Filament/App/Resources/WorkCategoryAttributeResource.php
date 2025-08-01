<?php

namespace App\Filament\App\Resources;

use App\Filament\App\Resources\WorkCategoryAttributeResource\Pages;
use App\Models\WorkCategory;
use App\Models\WorkCategoryAttribute;
use Filament\Facades\Filament;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class WorkCategoryAttributeResource extends Resource
{
    protected static ?string $model = WorkCategoryAttribute::class;

    protected static ?string $navigationIcon = 'heroicon-o-puzzle-piece';

    protected static ?string $navigationGroup = 'Workspace';

    protected static ?string $navigationLabel = 'Work Category Attributes';

    protected static ?string $modelLabel = 'Work Category Attribute';

    protected static ?string $modelLabelPlural = 'Work Category Attributes';

    protected static ?int $navigationSort = 3;

    protected static bool $isScopedToTenant = true;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('work_category_id')
                    ->label('Work Category')
                    ->options(function () {
                        return WorkCategory::where('is_active', true)
                            ->pluck('name', 'id');
                    })
                    ->required()
                    ->searchable(),

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

                Hidden::make('order')
                    ->default(function () {
                        return WorkCategoryAttribute::count() + 1;
                    }),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->reorderable('order')
            ->defaultSort('order')
            ->columns([
                TextColumn::make('workCategory.name')
                    ->label('Category')
                    ->searchable()
                    ->sortable(),

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
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
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
            'index' => Pages\ListWorkCategoryAttributes::route('/'),
            'create' => Pages\CreateWorkCategoryAttribute::route('/create'),
            'edit' => Pages\EditWorkCategoryAttribute::route('/{record}/edit'),
            'view' => Pages\ViewWorkCategoryAttribute::route('/{record}'),
        ];
    }
}
