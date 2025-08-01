<?php

namespace App\Filament\App\Resources\WorkOrderResource\Pages;

use App\Filament\App\Resources\WorkOrderResource;
use App\Models\Customer;
use App\Services\WorkOrder\DynamicAttributesService;
use Filament\Actions\DeleteAction;
use Filament\Facades\Filament;
use Filament\Resources\Pages\EditRecord;

class EditWorkOrder extends EditRecord
{
    protected static string $resource = WorkOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        if ($this->record->customer) {
            $data['customer_name'] = $this->record->customer->name;
            $data['customer_email'] = $this->record->customer->email;
            $data['customer_phone'] = $this->record->customer->phone;
        }
        // Load attribute values
        $attributesService = app(DynamicAttributesService::class);
        $data['attribute_values'] = $attributesService->getAttributeValuesForForm($this->record);

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Handle customer creation or selection
        if (empty($data['customer_id']) && !empty($data['customer_name'])) {
            // Create a new customer
            $customer = Customer::create([
                'name' => $data['customer_name'],
                'email' => $data['customer_email'] ?? null,
                'phone' => $data['customer_phone'] ?? null,
                'organization_id' => Filament::getTenant()->id,
            ]);

            $data['customer_id'] = $customer->id;
        }

        // Remove unneeded form data
        unset($data['customer_name'], $data['customer_email'], $data['customer_phone']);

        return $data;
    }

    protected function afterSave(): void
    {
        // Save the dynamic attribute values
        $attributesService = app(DynamicAttributesService::class);
        $attributesService->saveAttributeValues($this->record, $this->data);
    }
}
