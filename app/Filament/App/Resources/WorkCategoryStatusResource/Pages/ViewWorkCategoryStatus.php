<?php

namespace App\Filament\App\Resources\WorkCategoryStatusResource\Pages;

use App\Filament\App\Resources\WorkCategoryStatusResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewWorkCategoryStatus extends ViewRecord
{
    protected static string $resource = WorkCategoryStatusResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
            DeleteAction::make(),
        ];
    }
}
