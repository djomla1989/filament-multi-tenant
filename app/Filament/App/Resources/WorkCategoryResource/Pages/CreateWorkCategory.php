<?php

namespace App\Filament\App\Resources\WorkCategoryResource\Pages;

use App\Filament\App\Resources\WorkCategoryResource;
use Filament\Resources\Pages\CreateRecord;

class CreateWorkCategory extends CreateRecord
{
    protected static string $resource = WorkCategoryResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
