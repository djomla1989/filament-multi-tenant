<?php

namespace App\Filament\Admin\Resources\TicketResource\Pages;

use App\Filament\Admin\Resources\TicketResource;
use App\Models\{Ticket};
use Filament\Actions;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditTicket extends EditRecord
{
    protected static string $resource = TicketResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
    protected function afterSave(): void
    {
        $ticket = $this->record->fresh(); // Reload the updated ticket from the database

        $status = strtolower(trim($ticket->status->value)); // Normalize enum value

        if (in_array($status, ['resolved', 'closed'])) {
            $ticket->update(['closed_at' => now()]); // Directly update the 'closed_at' field
        }

        // Find the user instance related to the ticket
        $user = $ticket->user;

        if ($user) { // Ensure the user exists
            Notification::make()
            ->title('Ticket Updated')
            ->body("Your ticket No. {$ticket->id} has been updated. Check the updates.")
            ->success()
            ->actions([
                Action::make('View')
                    ->url(TicketResource::getUrl('view', ['record' => $ticket->id]))
                    ->button(),
            ])
            ->sendToDatabase($user);
        }
    }
}
