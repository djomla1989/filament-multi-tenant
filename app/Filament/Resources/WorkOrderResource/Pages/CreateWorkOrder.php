<?php

namespace App\Filament\Resources\WorkOrderResource\Pages;

use App\Filament\Resources\WorkOrderResource;
use App\Models\Customer;
use App\Services\WorkOrder\WorkOrderService;
use Filament\Actions\CreateAction;
use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateWorkOrder extends CreateRecord
{
    protected static string $resource = WorkOrderResource::class;

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

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
