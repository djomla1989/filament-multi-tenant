<?php

namespace App\Notifications;

use App\Models\WorkOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WorkOrderStatusUpdated extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * The work order instance.
     *
     * @var WorkOrder
     */
    protected WorkOrder $workOrder;

    /**
     * Additional data for the notification.
     *
     * @var array
     */
    protected array $data;

    /**
     * Create a new notification instance.
     *
     * @param WorkOrder $workOrder The work order to notify about
     * @param array $data Additional data for the notification
     */
    public function __construct(WorkOrder $workOrder, array $data = [])
    {
        $this->workOrder = $workOrder;
        $this->data = $data;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $organization = $this->workOrder->organization;
        $workOrder = $this->workOrder;
        $trackingUrl = $workOrder->getTrackingUrl();
        $notes = $this->data['notes'] ?? null;

        $mail = (new MailMessage)
            ->subject("[{$organization->name}] Your Order #{$workOrder->order_number} has been updated")
            ->greeting("Hello {$workOrder->customer->name}!")
            ->line("Your order #{$workOrder->order_number} has been updated.")
            ->line("Order: {$workOrder->title}")
            ->line("Category: {$workOrder->workCategory->name}")
            ->line("Current Status: {$workOrder->currentStatus->name}");

        if ($notes) {
            $mail->line("Notes: {$notes}");
        }

        return $mail
            ->action('Track Your Order', $trackingUrl)
            ->line('You can use the link above to track the status of your order at any time.')
            ->line('Thank you for your business!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'work_order_id' => $this->workOrder->id,
            'order_number' => $this->workOrder->order_number,
            'title' => $this->workOrder->title,
            'status' => $this->workOrder->currentStatus->name,
            'tracking_url' => $this->workOrder->getTrackingUrl(),
            'notes' => $this->data['notes'] ?? null,
        ];
    }
}
