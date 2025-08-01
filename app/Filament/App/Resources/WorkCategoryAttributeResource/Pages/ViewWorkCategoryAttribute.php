<?php

namespace App\Filament\App\Resources\WorkCategoryAttributeResource\Pages;

use App\Filament\App\Resources\WorkCategoryAttributeResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewWorkCategoryAttribute extends ViewRecord
{
    protected static string $resource = WorkCategoryAttributeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
            DeleteAction::make(),
        ];
    }
}
