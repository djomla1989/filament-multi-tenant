<?php

namespace App\Services\WorkOrder;

use App\Models\Customer;
use App\Models\WorkOrder;
use App\Models\WorkOrderHistory;
use App\Services\Notifications\NotificationService;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\DB;

class WorkOrderService
{
    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Create a new work order.
     *
     * @param array $data The work order data
     * @param \App\Models\User $user The user creating the work order
     * @return \App\Models\WorkOrder The created work order
     */
    public function createWorkOrder(array $data, $user)
    {
        return DB::transaction(function () use ($data, $user) {
            // Create or use customer
            if (empty($data['customer_id']) && !empty($data['customer_name'])) {
                $customer = Customer::create([
                    'name' => $data['customer_name'],
                    'email' => $data['customer_email'] ?? null,
                    'phone' => $data['customer_phone'] ?? null,
                    'organization_id' => Filament::getTenant()->id,
                    'user_id' => null, // If you want to link to a user account
                ]);
                $data['customer_id'] = $customer->id;
            }

            // Set up work order data
            $workOrderData = [
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'customer_id' => $data['customer_id'],
                'work_category_id' => $data['work_category_id'],
                'current_status_id' => $data['current_status_id'],
                'notification_channel' => $data['notification_channel'] ?? 'email',
                'notification_preferences' => [], // Default all notifications to enabled
                'estimated_completion_date' => $data['estimated_completion_date'] ?? null,
                'created_by_id' => $user->id,
                'organization_id' => Filament::getTenant()->id,
            ];

            // Create work order
            $workOrder = WorkOrder::create($workOrderData);

            // Create initial history entry
            WorkOrderHistory::create([
                'work_order_id' => $workOrder->id,
                'status_id' => $data['current_status_id'],
                'notes' => 'Work order created',
                'is_public' => true,
                'created_by_id' => $user->id,
            ]);

            // Create custom details if provided
            if (!empty($data['details'])) {
                foreach ($data['details'] as $key => $value) {
                    if (!empty($value)) {
                        $workOrder->details()->create([
                            'key' => str_replace('_', ' ', ucfirst($key)),
                            'value' => $value,
                        ]);
                    }
                }
            }

            // Send tracking notification
            $this->notificationService->sendTrackingNotification($workOrder);

            return $workOrder;
        });
    }

    /**
     * Update a work order's status.
     *
     * @param \App\Models\WorkOrder $workOrder The work order to update
     * @param int $statusId The new status ID
     * @param \App\Models\User $user The user updating the status
     * @param string $notes Notes about the status change
     * @param bool $isPublic Whether the notes are visible to the customer
     * @param bool $notifyCustomer Whether to send a notification to the customer
     * @return \App\Models\WorkOrder The updated work order
     */
    public function updateWorkOrderStatus(
        WorkOrder $workOrder,
        int $statusId,
        $user,
        string $notes,
        bool $isPublic = true,
        bool $notifyCustomer = true
    ) {
        return DB::transaction(function () use ($workOrder, $statusId, $user, $notes, $isPublic, $notifyCustomer) {
            // Only update if status actually changed
            $statusChanged = $workOrder->current_status_id !== $statusId;

            // Create history entry
            $historyEntry = WorkOrderHistory::create([
                'work_order_id' => $workOrder->id,
                'status_id' => $statusId,
                'notes' => $notes,
                'is_public' => $isPublic,
                'created_by_id' => $user->id,
            ]);

            // Update work order status if changed
            if ($statusChanged) {
                $workOrder->current_status_id = $statusId;
                $workOrder->save();
            }

            // Send notification if requested and preferences allow
            if ($notifyCustomer && $isPublic) {
                // Only send notification if enabled for the channel
                $channel = $workOrder->notification_channel;

                // Check if notifications are enabled for this channel
                if ($workOrder->isNotificationEnabled($channel)) {
                    $this->notificationService->sendStatusUpdateNotification($workOrder, [
                        'history_entry' => $historyEntry,
                    ]);
                }
            }

            return $workOrder;
        });
    }
}
