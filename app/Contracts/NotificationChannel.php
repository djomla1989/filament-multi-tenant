<?php

namespace App\Contracts;

use App\Models\WorkOrder;

interface NotificationChannel
{
    /**
     * Send a work order tracking notification.
     *
     * @param WorkOrder $workOrder The work order to notify about
     * @return bool Whether the notification was sent successfully
     */
    public function sendTrackingNotification(WorkOrder $workOrder): bool;

    /**
     * Send a work order status update notification.
     *
     * @param WorkOrder $workOrder The work order to notify about
     * @param array $data Additional data for the notification
     * @return bool Whether the notification was sent successfully
     */
    public function sendStatusUpdateNotification(WorkOrder $workOrder, array $data = []): bool;

    /**
     * Get the notification channel name.
     *
     * @return string The channel name
     */
    public function getChannelName(): string;
}
