<?php

namespace App\Http\Controllers;

use App\Enums\Evolution\StatusConnectionEnum;
use App\Models\{Organization, WebhookEvent, WhatsappInstance};
use App\Services\Evolution\Instance\FetchEvolutionInstanceService;
use Filament\Notifications\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class EvolutionWebhookController extends Controller
{
    public function handle(Request $request)
    {
        // Get the payload content and signature header
        $payload = $request->getContent();

        // Store the received event in the database
        WebhookEvent::create([
            'event_type' => $request->input('event'),
            'payload'    => json_encode($request->all()),
            'status'     => 'success',
        ]);

        // Process the event according to its type
        $eventType = $request->input('event');

        // Processing based on event type
        switch ($eventType) {

            case 'connection.update':
                return $this->handleConnectionStatus($request->all());

            case 'qrcode.updated':
                return $this->handleQrcodeUpdated($request->all());

            case 'messages.upsert':
                return $this->handleMessagesUpsert($request->all());

            case 'new.token':
                return $this->handleNewToken($request->all());

            case 'send.message':
                return $this->handleSendMessage($request->all());

            case 'messages.update':
                return $this->handleMessagesUpdate($request->all());

            case 'logout.instance':
                return $this->handleLogoutInstance($request->all());

            case 'remove.instance':
                return $this->handleRemoveInstance($request->all());

            case 'presence.update':
                return $this->handlePresenceUpdate($request->all());
            default:
                break;
        }
    }

    // Handles the WhatsApp connection status event
    private function handleConnectionStatus($data)
    {
        $instance = WhatsappInstance::where('name', $data['instance'])->first();

        // Check if the instance exists
        if (!$instance) {
            return; // if no instance is found, abort flow to avoid repeating notifications
        }

        // Check if the current instance state matches the webhook state
        if ($data['data']['state'] === $instance->status) {
            return; // if it has the same status, abort flow to avoid repeating notifications
        }

        // If the state is Connected, Disconnected, or Refused, clear the QR Code from the table
        if ($data['data']['state'] === 'open' || $data['data']['state'] === 'close' || $data['data']['state'] === 'refused') {
            $instance->update(['qr_code' => null]);
        }

        // Update the instance status
        $instance->update(['status' => $data['data']['state']]);

        // Call service to update the instance profile picture
        if ($data['data']['state'] === 'open') {
            $fetchService = new FetchEvolutionInstanceService();
            $fetchService->fetchInstance($data['instance']);
        }

        // Find the organization admin
        $organization = Organization::find($instance->organization_id);
        $adminUser    = $organization->members()->where('is_tenant_admin', true)->first();

        // Translate the status with the Connection Enum
        $stateLabel = StatusConnectionEnum::tryFrom($data['data']['state'])->getLabel();

        // Send notification to the tenant admin
        Notification::make()
            ->title('Instance Status Updated')
            ->body("The instance {$data['instance']} had its status updated to {$stateLabel}.")
            ->sendToDatabase($adminUser);

    }
    // Handles the WhatsApp QR code update event
    private function handleQrcodeUpdated($data)
    {
        // Verifica se há um erro no retorno do webhook
        if (isset($data['message']) && isset($data['statusCode'])) {
            Log::error("Erro no evento QRCODE_UPDATED: {$data['message']} (Código: {$data['statusCode']})");

            $instance = WhatsappInstance::where('name', $data['instance'] ?? null)->first();

            if ($instance) {
                $organization = Organization::find($instance->organization_id);

                if ($organization) {
                    $adminUser = $organization->members()->where('is_tenant_admin', true)->first();

                    if ($adminUser) {
                        Notification::make()
                            ->title('Error Updating QR Code')
                            ->body("The instance {$data['instance']} encountered an error: {$data['message']}. Try logging in again.")
                            ->sendToDatabase($adminUser);
                    }
                }
            }

            return;
        }

        // Check if the webhook contains the necessary data
        if (empty($data['data']['qrcode']['base64']) || empty($data['instance'])) {
            Log::warning('QRCODE_UPDATED event received with incomplete data: ' . json_encode($data));

            return;
        }

        // Find the WhatsApp instance
        $instance = WhatsappInstance::where('name', $data['instance'])->first();

        if (!$instance) {
            Log::warning("No instance found for '{$data['instance']}' in the QRCODE_UPDATED event.");

            return;
        }

        // Update the QR Code in the instance
        $instance->update([
            'qr_code'      => $data['data']['qrcode']['base64'],
            'pairing_code' => $data['data']['qrcode']['pairingCode'] ?? '',
            'updated_at'   => now(),
        ]);

        Log::info("QR Code atualizado para {$data['data']['qrcode']['base64']}");

        // Find the organization and the organization administrator
        $organization = Organization::find($instance->organization_id);

        if (!$organization) {
            Log::warning("No organization found for the instance {$data['instance']}.");

            return;
        }

        $adminUser = $organization->members()->where('is_tenant_admin', true)->first();

        if (!$adminUser) {
            Log::warning("No administrator found for organization ID {$organization->id}.");

            return;
        }

        // Send the notification
        Notification::make()
            ->title('New QR Code Available')
            ->body("The instance {$data['instance']} generated a new QR Code. Use it to authenticate your account.")
            ->sendToDatabase($adminUser);
    }

    // Handles the MESSAGES_UPSERT event
    private function handleMessagesUpsert($data)
    {

    }

    // Handles the NEW_TOKEN event
    private function handleNewToken($data)
    {

    }

    // Handles the SEND_MESSAGE event
    private function handleSendMessage($data)
    {

    }

    // Handles the MESSAGES_UPDATE event
    private function handleMessagesUpdate($data)
    {

    }

    // Handles the LOGOUT_INSTANCE event
    private function handleLogoutInstance($data)
    {

    }

    // Handles the REMOVE_INSTANCE event
    private function handleRemoveInstance($data)
    {

    }

    // Handles the PRESENCE_UPDATE event
    private function handlePresenceUpdate($data)
    {

    }

}
