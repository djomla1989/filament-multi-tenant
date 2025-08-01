<?php

namespace App\Http\Controllers;

use App\Models\WorkOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class WorkOrderTrackingController extends Controller
{
    /**
     * Show the public tracking page for a work order.
     */
    public function show(string $trackingToken): View
    {
        try {
            $workOrder = WorkOrder::where('tracking_token', $trackingToken)
                ->with(['customer', 'workCategory', 'currentStatus', 'organization', 'history.status', 'history.createdBy'])
                ->firstOrFail();

            $historyItems = $workOrder->getPublicHistoryItems();
            $availableChannels = $workOrder->getAvailableNotificationChannels();

            return view('tracking.show', compact('workOrder', 'historyItems', 'availableChannels'));
        } catch (\Exception $e) {
            // Log the error
            \Illuminate\Support\Facades\Log::error('Tracking page error: ' . $e->getMessage(), [
                'trackingToken' => $trackingToken,
                'exception' => $e,
            ]);

            // Return a nice error page
            return view('tracking.error', ['message' => 'Work order not found or no longer available']);
        }
    }

    /**
     * Update notification preferences for a work order.
     */
    public function updateNotificationPreferences(Request $request, string $trackingToken)
    {
        $workOrder = WorkOrder::where('tracking_token', $trackingToken)->firstOrFail();

        $validated = $request->validate([
            'channel' => 'required|string',
            'enabled' => 'required|boolean',
        ]);

        $availableChannels = $workOrder->getAvailableNotificationChannels();

        // Check if the requested channel is available for this work order
        if (!array_key_exists($validated['channel'], $availableChannels)) {
            return response()->json([
                'success' => false,
                'message' => 'This notification channel is not available',
            ], 400);
        }

        $workOrder->updateNotificationPreference($validated['channel'], $validated['enabled']);

        return response()->json([
            'success' => true,
            'message' => 'Notification preferences updated successfully',
        ]);
    }
}
