<?php

namespace App\Filament\App\Resources\WorkCategoryAttributeResource\Pages;

use App\Filament\App\Resources\WorkCategoryAttributeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListWorkCategoryAttributes extends ListRecords
{
    protected static string $resource = WorkCategoryAttributeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
