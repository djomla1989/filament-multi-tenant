<?php

namespace App\Services\Notifications\Channels;

use App\Contracts\NotificationChannel;
use App\Models\WorkOrder;
use App\Notifications\WorkOrderStatusUpdated;
use App\Notifications\WorkOrderTracking;
use Illuminate\Support\Facades\Notification;

class EmailNotificationChannel implements NotificationChannel
{
    /**
     * Send a work order tracking notification via email.
     *
     * @param WorkOrder $workOrder The work order to notify about
     * @return bool Whether the notification was sent successfully
     */
    public function sendTrackingNotification(WorkOrder $workOrder): bool
    {
        if (empty($workOrder->customer->email)) {
            return false;
        }

        try {
            Notification::route('mail', $workOrder->customer->email)
                ->notify(new WorkOrderTracking($workOrder));
            return true;
        } catch (\Exception $e) {
            report($e);
            return false;
        }
    }

    /**
     * Send a work order status update notification via email.
     *
     * @param WorkOrder $workOrder The work order to notify about
     * @param array $data Additional data for the notification
     * @return bool Whether the notification was sent successfully
     */
    public function sendStatusUpdateNotification(WorkOrder $workOrder, array $data = []): bool
    {
        if (empty($workOrder->customer->email)) {
            return false;
        }

        try {
            Notification::route('mail', $workOrder->customer->email)
                ->notify(new WorkOrderStatusUpdated($workOrder, $data));
            return true;
        } catch (\Exception $e) {
            report($e);
            return false;
        }
    }

    /**
     * Get the notification channel name.
     *
     * @return string The channel name
     */
    public function getChannelName(): string
    {
        return 'email';
    }
}
