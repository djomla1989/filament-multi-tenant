<?php

namespace App\Filament\App\Resources\WorkCategoryAttributeResource\Pages;

use App\Filament\App\Resources\WorkCategoryAttributeResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditWorkCategoryAttribute extends EditRecord
{
    protected static string $resource = WorkCategoryAttributeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
