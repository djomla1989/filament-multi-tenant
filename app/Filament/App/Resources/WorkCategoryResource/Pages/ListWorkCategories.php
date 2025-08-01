<?php

namespace App\Filament\App\Resources\WorkCategoryResource\Pages;

use App\Filament\App\Resources\WorkCategoryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListWorkCategories extends ListRecords
{
    protected static string $resource = WorkCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
