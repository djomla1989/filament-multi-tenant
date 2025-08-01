<?php

namespace App\Notifications;

use App\Models\WorkOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WorkOrderTracking extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * The work order instance.
     *
     * @var WorkOrder
     */
    protected WorkOrder $workOrder;

    /**
     * Create a new notification instance.
     *
     * @param WorkOrder $workOrder The work order to notify about
     */
    public function __construct(WorkOrder $workOrder)
    {
        $this->workOrder = $workOrder;
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

        return (new MailMessage)
            ->subject("[{$organization->name}] Your Order #{$workOrder->order_number} has been created")
            ->greeting("Hello {$workOrder->customer->name}!")
            ->line("Your order #{$workOrder->order_number} has been created and is now being processed.")
            ->line("Order: {$workOrder->title}")
            ->line("Category: {$workOrder->workCategory->name}")
            ->line("Current Status: {$workOrder->currentStatus->name}")
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
            'tracking_url' => $this->workOrder->getTrackingUrl(),
        ];
    }
}
