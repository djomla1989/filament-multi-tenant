<?php

namespace App\Filament\App\Resources\WorkOrderResource\Pages;

use App\Filament\App\Resources\WorkOrderResource;
use App\Services\WorkOrder\WorkOrderService;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
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
                ->color('primary')
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

                    Notification::make()
                        ->success()
                        ->title('Status updated successfully')
                        ->send();

                    $this->redirect(WorkOrderResource::getUrl('view', ['record' => $this->record]));
                }),

            DeleteAction::make(),
        ];
    }
}
