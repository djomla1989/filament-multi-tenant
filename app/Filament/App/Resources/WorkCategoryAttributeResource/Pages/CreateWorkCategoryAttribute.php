<?php

namespace App\Filament\App\Resources\WorkCategoryAttributeResource\Pages;

use App\Filament\App\Resources\WorkCategoryAttributeResource;
use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;

class CreateWorkCategoryAttribute extends CreateRecord
{
    protected static string $resource = WorkCategoryAttributeResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['organization_id'] = Filament::getTenant()->id;
        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
