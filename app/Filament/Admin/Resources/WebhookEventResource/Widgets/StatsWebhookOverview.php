<?php

namespace App\Filament\Admin\Resources\WebhookEventResource\Widgets;

use App\Models\WebhookEvent;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsWebhookOverview extends BaseWidget
{
    protected static bool $isLazy = true;

    protected function getStats(): array
    {
        $successCount = WebhookEvent::where('status', 'success')->count();
        $failureCount = WebhookEvent::where('status', 'failed')->count();

        // Calculate the average failure rate over successes, avoiding division by zero
        $failureRate = $successCount > 0 ? $failureCount / $successCount : 0;

        return [
            Stat::make('Failed Webhooks', $failureCount)
                ->description('Total')
                ->descriptionIcon('heroicon-s-bug-ant')
                ->color('danger')
                ->chart([7, 3, 4, 5, 6, 3, 5, 8]),

            Stat::make('Successful Webhooks', $successCount)
                ->description('Total')
                ->descriptionIcon('heroicon-s-check-badge')
                ->color('success')
                ->chart([7, 3, 4, 5, 6, 3, 5, 3]),

            Stat::make('Failure Rate', number_format($failureRate, 2) . ' %')
                ->description('Failures / Successes')
                ->color('warning')
                ->descriptionIcon('heroicon-s-exclamation-triangle')
                ->chart([3, 2, 1, 4, 2, 1, 3, 2]),

        ];
    }

}
