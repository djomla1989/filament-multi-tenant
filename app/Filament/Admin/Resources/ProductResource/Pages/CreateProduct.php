<?php

namespace App\Filament\Admin\Resources\ProductResource\Pages;

use App\Filament\Admin\Resources\ProductResource;
use App\Services\Stripe\Product\CreateStripeProductService;
use Filament\Resources\Pages\CreateRecord;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        try {
            $createStripeProductService = new CreateStripeProductService();

            $data['stripe_id'] = $createStripeProductService->execute(
                $data['name'],
                $data['description'] ?? null
            );

            return $data;
        } catch (\Exception $e) {
            throw new \Exception('Error saving product: ' . $e->getMessage());
        }
    }
}
