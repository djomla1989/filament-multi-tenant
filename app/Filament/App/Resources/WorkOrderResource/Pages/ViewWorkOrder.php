<?php

namespace App\Filament\App\Resources\WorkOrderResource\Pages;

use App\Filament\App\Resources\WorkOrderResource;
use App\Services\WorkOrder\WorkOrderService;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewWorkOrder extends ViewRecord
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
                    Select::make('status_id')
                        ->label('New Status')
                        ->options(function () {
                            return $this->record->workCategory->statuses
                                ->where('is_active', true)
                                ->pluck('name', 'id');
                        })
                        ->default(fn () => $this->record->current_status_id)
                        ->required(),
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
                        $data['status_id'],
                        auth()->user(),
                        $data['notes'],
                        $data['is_public'],
                        $data['notify_customer']
                    );

                    Notification::make()
                        ->success()
                        ->title('Status updated successfully')
                        ->send();

                    $this->redirect(static::getResource()::getUrl('view', ['record' => $this->record]));
                }),

            Action::make('viewQrCode')
                ->label('View QR Code')
                ->icon('heroicon-o-qr-code')
                ->color('success')
                ->modalContent(fn () => view('filament.resources.work-order.qr-code', [
                    'workOrder' => $this->record,
                    'qrCode' => $this->record->getQrCode(300),
                    'trackingUrl' => $this->record->getTrackingUrl(),
                ]))
                ->modalSubmitAction(false),

            EditAction::make(),
        ];
    }
}
