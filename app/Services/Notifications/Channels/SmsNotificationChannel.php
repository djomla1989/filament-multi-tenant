<?php

namespace App\Services\Notifications\Channels;

use App\Contracts\NotificationChannel;
use App\Models\WorkOrder;

class SmsNotificationChannel implements NotificationChannel
{
    /**
     * Send a work order tracking notification via SMS.
     *
     * @param WorkOrder $workOrder The work order to notify about
     * @return bool Whether the notification was sent successfully
     */
    public function sendTrackingNotification(WorkOrder $workOrder): bool
    {
        if (empty($workOrder->customer->phone)) {
            return false;
        }

        // This is a placeholder for actual SMS implementation
        // You would integrate with an SMS provider like Twilio, Vonage, etc.
        try {
            // Example implementation:
            // $message = "Your order {$workOrder->order_number} has been created. Track it at: {$workOrder->getTrackingUrl()}";
            // return $this->sendSms($workOrder->customer->phone, $message);

            return true; // Placeholder return
        } catch (\Exception $e) {
            report($e);
            return false;
        }
    }

    /**
     * Send a work order status update notification via SMS.
     *
     * @param WorkOrder $workOrder The work order to notify about
     * @param array $data Additional data for the notification
     * @return bool Whether the notification was sent successfully
     */
    public function sendStatusUpdateNotification(WorkOrder $workOrder, array $data = []): bool
    {
        if (empty($workOrder->customer->phone)) {
            return false;
        }

        // This is a placeholder for actual SMS implementation
        try {
            // Example implementation:
            // $status = $workOrder->currentStatus->name;
            // $message = "Your order {$workOrder->order_number} status has been updated to: {$status}. Track it at: {$workOrder->getTrackingUrl()}";
            // return $this->sendSms($workOrder->customer->phone, $message);

            return true; // Placeholder return
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
        return 'sms';
    }

    /**
     * Send an SMS message to the given number.
     *
     * @param string $phoneNumber The recipient's phone number
     * @param string $message The message to send
     * @return bool Whether the SMS was sent successfully
     */
    private function sendSms(string $phoneNumber, string $message): bool
    {
        // Implement SMS sending logic here using your preferred SMS provider
        // Example with Twilio:
        //
        // $twilioSid = config('services.twilio.sid');
        // $twilioToken = config('services.twilio.token');
        // $twilioFrom = config('services.twilio.from');
        //
        // $twilio = new Client($twilioSid, $twilioToken);
        // $twilio->messages->create($phoneNumber, [
        //     'from' => $twilioFrom,
        //     'body' => $message
        // ]);
        //
        // return true;

        return true; // Placeholder return
    }
}
