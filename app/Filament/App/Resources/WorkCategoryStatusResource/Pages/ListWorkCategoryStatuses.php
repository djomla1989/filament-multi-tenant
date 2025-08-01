<?php

namespace App\Filament\App\Resources\WorkCategoryStatusResource\Pages;

use App\Filament\App\Resources\WorkCategoryStatusResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListWorkCategoryStatuses extends ListRecords
{
    protected static string $resource = WorkCategoryStatusResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
