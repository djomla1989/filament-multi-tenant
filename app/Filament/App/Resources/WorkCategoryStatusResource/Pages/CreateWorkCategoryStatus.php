<?php

namespace App\Filament\App\Resources\WorkCategoryStatusResource\Pages;

use App\Filament\App\Resources\WorkCategoryStatusResource;
use Filament\Resources\Pages\CreateRecord;

class CreateWorkCategoryStatus extends CreateRecord
{
    protected static string $resource = WorkCategoryStatusResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
