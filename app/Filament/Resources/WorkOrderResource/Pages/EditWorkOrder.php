<?php

namespace App\Filament\Resources\WorkOrderResource\Pages;

use App\Filament\Resources\WorkOrderResource;
use App\Services\WorkOrder\WorkOrderService;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Pages\EditRecord;
use Filament\Actions\Action;

class EditWorkOrder extends EditRecord
{
    protected static string $resource = WorkOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('updateStatus')
                ->label('Update Status')
                ->icon('heroicon-o-arrow-path')
                ->form([
                    Textarea::make('notes')
                        ->label('Status Update Notes')
                        ->required(),
                    Toggle::make('is_public')
                        ->label('Visible to Customer')
                        ->default(true),
                    Toggle::make('notify_customer')
                        ->label('Notify Customer')
                        ->default(true),
                ])
                ->action(function (array $data): void {
                    $workOrderService = app(WorkOrderService::class);

                    $workOrderService->updateWorkOrderStatus(
                        $this->record,
                        $this->record->current_status_id, // Use the current status from the form
                        auth()->user(),
                        $data['notes'],
                        $data['is_public'],
                        $data['notify_customer']
                    );

                    $this->notify('success', 'Status updated successfully');
                    $this->redirect(WorkOrderResource::getUrl('view', ['record' => $this->record]));
                }),

            DeleteAction::make(),
        ];
    }
}
