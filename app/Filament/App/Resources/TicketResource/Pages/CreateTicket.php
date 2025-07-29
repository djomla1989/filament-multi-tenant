<?php

namespace App\Filament\App\Resources\TicketResource\Pages;

use App\Filament\App\Resources\TicketResource;
use App\Models\Ticket;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateTicket extends CreateRecord
{
    protected static string $resource = TicketResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = Auth::user()->id;

        return $data;
    }

    protected function afterCreate(): void
    {
        $ticket = $this->record; // Retrieve the newly created or updated ticket

        Notification::make()
            ->title('Ticket Successfully Created')
            ->body("Your ticket No. {$ticket->id} has been successfully created. It will be answered shortly.")
            ->success()
            ->actions([
                Action::make('View')
                    ->url(TicketResource::getUrl('view', ['record' => $ticket->id])),

            ])
            ->sendToDatabase(Auth::user()); // Sends notification to the user related to the ticket

    }

}
