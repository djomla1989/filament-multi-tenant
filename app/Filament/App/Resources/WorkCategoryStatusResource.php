<?php

namespace App\Filament\App\Resources;

use App\Filament\App\Resources\WorkCategoryStatusResource\Pages;
use App\Models\WorkCategory;
use App\Models\WorkCategoryStatus;
use Filament\Facades\Filament;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Hidden;
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

class WorkCategoryStatusResource extends Resource
{
    protected static ?string $model = WorkCategoryStatus::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';

    protected static ?string $navigationGroup = 'Workspace';

    protected static ?string $navigationLabel = 'Work Category Statuses';

    protected static ?string $modelLabel = 'Work Category Status';

    protected static ?string $modelLabelPlural = 'Work Category Statuses';

    protected static ?int $navigationSort = 2;

    protected static bool $isScopedToTenant = true;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('work_category_id')
                    ->label('Work Category')
                    ->options(function () {
                        return WorkCategory::where('is_active', true)
                            //->where('organization_id', Filament::getTenant()->id)
                            ->pluck('name', 'id');
                    })
                    ->required()
                    ->searchable(),

                TextInput::make('name')
                    ->required()
                    ->maxLength(255),

                Textarea::make('description')
                    ->maxLength(65535)
                    ->columnSpanFull(),

                Checkbox::make('is_active')
                    ->label('Active')
                    ->default(true),

                Hidden::make('order')
                    ->default(function () {
                        return WorkCategoryStatus::count() + 1;
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

                TextColumn::make('description')
                    ->limit(50)
                    ->searchable(),

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
            'index' => Pages\ListWorkCategoryStatuses::route('/'),
            'create' => Pages\CreateWorkCategoryStatus::route('/create'),
            'edit' => Pages\EditWorkCategoryStatus::route('/{record}/edit'),
            'view' => Pages\ViewWorkCategoryStatus::route('/{record}'),
        ];
    }
}
