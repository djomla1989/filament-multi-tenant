<?php

use App\Http\Controllers\{EvolutionWebhookController, StripeWebhookController, WorkOrderTrackingController};
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/app');
});

//Rota do webhook custom stripe
Route::post('/stripe/webhook', [StripeWebhookController::class, 'handle']);

//Rota do webhook custom evolution
Route::post('/evolution/webhook', [EvolutionWebhookController::class, 'handle']);

// Public work order tracking routes
Route::get('/track/{trackingToken}', [WorkOrderTrackingController::class, 'show'])
    ->name('tracking.show');

Route::post('/track/{trackingToken}/notifications', [WorkOrderTrackingController::class, 'updateNotificationPreferences'])
    ->name('tracking.update-notifications');
