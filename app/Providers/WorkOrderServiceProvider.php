<?php

namespace App\Providers;

use App\Contracts\NotificationChannel;
use App\Services\Notifications\Channels\EmailNotificationChannel;
use App\Services\Notifications\Channels\SmsNotificationChannel;
use App\Services\Notifications\Channels\ViberNotificationChannel;
use App\Services\Notifications\Channels\WhatsAppNotificationChannel;
use App\Services\Notifications\NotificationService;
use App\Services\QrCode\QrCodeGenerator;
use App\Services\WorkOrder\WorkOrderService;
use Illuminate\Support\ServiceProvider;

class WorkOrderServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register notification channels
        $this->app->bind(NotificationChannel::class . 'email', EmailNotificationChannel::class);
        $this->app->bind(NotificationChannel::class . 'sms', SmsNotificationChannel::class);
        $this->app->bind(NotificationChannel::class . 'whatsapp', WhatsAppNotificationChannel::class);
        $this->app->bind(NotificationChannel::class . 'viber', ViberNotificationChannel::class);

        // Register the notification service
        $this->app->singleton(NotificationService::class, function ($app) {
            return new NotificationService();
        });

        // Register the QR code generator
        $this->app->singleton(QrCodeGenerator::class, function ($app) {
            return new QrCodeGenerator();
        });

        // Register the work order service
        $this->app->singleton(WorkOrderService::class, function ($app) {
            return new WorkOrderService(
                $app->make(NotificationService::class)
            );
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
