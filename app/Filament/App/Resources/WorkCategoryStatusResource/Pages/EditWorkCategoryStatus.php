<?php

namespace App\Filament\App\Resources\WorkCategoryStatusResource\Pages;

use App\Filament\App\Resources\WorkCategoryStatusResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditWorkCategoryStatus extends EditRecord
{
    protected static string $resource = WorkCategoryStatusResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
