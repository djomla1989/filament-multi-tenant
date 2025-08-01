<?php

namespace App\Filament\App\Resources\WorkOrderResource\Pages;

use App\Filament\App\Resources\WorkOrderResource;
use App\Models\Customer;
use App\Services\WorkOrder\DynamicAttributesService;
use App\Services\WorkOrder\WorkOrderService;
use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateWorkOrder extends CreateRecord
{
    protected static string $resource = WorkOrderResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
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

        // Generate unique order number
        //$data = app(WorkOrderService::class)->createWorkOrder($data, auth()->user());

        // Set the tenant ID
        //$data['organization_id'] = Filament::getTenant()->id;
        // Remove unneeded form data
        unset($data['customer_name'], $data['customer_email'], $data['customer_phone']);

        return $data;
    }

    protected function handleRecordCreation(array $data): Model
    {
        $workOrderService = app(WorkOrderService::class);

        // If customer_id is not provided, use the customer_* fields to create a new customer
        if (empty($data['customer_id']) && !empty($data['customer_name'])) {
            $data['customer_id'] = null; // Will be created by the service
        }

        // Create the work order
        return $workOrderService->createWorkOrder($data, auth()->user());
    }

    protected function afterCreate(): void
    {
        // Save the dynamic attribute values
        $attributesService = app(DynamicAttributesService::class);
        $attributesService->saveAttributeValues($this->record, $this->data);
    }
}
