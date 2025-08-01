<?php

namespace App\Services\Notifications;

use App\Contracts\NotificationChannel;
use App\Models\WorkOrder;
use Illuminate\Support\Facades\App;

class NotificationService
{
    /**
     * @var array<string, NotificationChannel> The notification channels
     */
    protected array $channels = [];

    /**
     * Create a new notification service instance.
     */
    public function __construct()
    {
        $this->registerChannels();
    }

    /**
     * Register all available notification channels.
     */
    protected function registerChannels(): void
    {
        $channelClasses = [
            Channels\EmailNotificationChannel::class,
            Channels\SmsNotificationChannel::class,
            Channels\WhatsAppNotificationChannel::class,
            Channels\ViberNotificationChannel::class,
        ];

        foreach ($channelClasses as $class) {
            /** @var NotificationChannel $channel */
            $channel = App::make($class);
            $this->channels[$channel->getChannelName()] = $channel;
        }
    }

    /**
     * Send a work order tracking notification via the appropriate channel.
     *
     * @param WorkOrder $workOrder The work order to notify about
     * @return bool Whether the notification was sent successfully
     */
    public function sendTrackingNotification(WorkOrder $workOrder): bool
    {
        $channelName = $workOrder->notification_channel;

        if (!isset($this->channels[$channelName])) {
            return false;
        }

        return $this->channels[$channelName]->sendTrackingNotification($workOrder);
    }

    /**
     * Send a work order status update notification via the appropriate channel.
     *
     * @param WorkOrder $workOrder The work order to notify about
     * @param array $data Additional data for the notification
     * @return bool Whether the notification was sent successfully
     */
    public function sendStatusUpdateNotification(WorkOrder $workOrder, array $data = []): bool
    {
        $channelName = $workOrder->notification_channel;

        if (!isset($this->channels[$channelName])) {
            return false;
        }

        return $this->channels[$channelName]->sendStatusUpdateNotification($workOrder, $data);
    }

    /**
     * Get all available notification channels.
     *
     * @return array<string, NotificationChannel> The notification channels
     */
    public function getChannels(): array
    {
        return $this->channels;
    }

    /**
     * Get available notification channel options for forms.
     *
     * @return array<string, string> The channel options
     */
    public function getChannelOptions(): array
    {
        return [
            'email' => 'Email',
            'sms' => 'SMS',
            'whatsapp' => 'WhatsApp',
            'viber' => 'Viber',
        ];
    }
}
