<?php

namespace App\Models;

use App\Services\QrCode\QrCodeGenerator;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class WorkOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'title',
        'description',
        'customer_id',
        'work_category_id',
        'current_status_id',
        'tracking_token',
        'notification_channel',
        'notification_preferences',
        'estimated_completion_date',
        'created_by_id',
        'organization_id',
    ];

    protected $casts = [
        'estimated_completion_date' => 'datetime',
        'notification_preferences' => 'array',
    ];

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        // Apply tenant scope if user is authenticated
        static::addGlobalScope('organization', function (Builder $query) {
            if (auth()->hasUser()) {
                $query->where('organization_id', Filament::getTenant()->id);
            }
        });

        static::creating(function (WorkOrder $workOrder) {
            // Generate tracking token if not set
            if (empty($workOrder->tracking_token)) {
                $workOrder->tracking_token = Str::random(64);
            }

            // Generate order number if not set
            if (empty($workOrder->order_number)) {
                $prefix = 'WO';
                $year = date('Y');
                $month = date('m');

                $latestOrder = self::where('organization_id', $workOrder->organization_id)
                    ->where('order_number', 'like', "{$prefix}-{$year}{$month}-%")
                    ->orderBy('id', 'desc')
                    ->first();

                $number = 1;
                if ($latestOrder) {
                    $parts = explode('-', $latestOrder->order_number);
                    $number = (int)end($parts) + 1;
                }

                $workOrder->order_number = "{$prefix}-{$year}{$month}-" . str_pad($number, 4, '0', STR_PAD_LEFT);
            }
        });
    }

    /**
     * Get the customer that owns the work order.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the work category of the work order.
     */
    public function workCategory(): BelongsTo
    {
        return $this->belongsTo(WorkCategory::class);
    }

    /**
     * Get the current status of the work order.
     */
    public function currentStatus(): BelongsTo
    {
        return $this->belongsTo(WorkCategoryStatus::class, 'current_status_id');
    }

    /**
     * Get the user who created the work order.
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    /**
     * Get the organization that owns the work order.
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Get the history entries for the work order.
     */
    public function history(): HasMany
    {
        return $this->hasMany(WorkOrderHistory::class)->orderBy('created_at', 'desc');
    }

    /**
     * Get the detail entries for the work order.
     */
    public function details(): HasMany
    {
        return $this->hasMany(WorkOrderDetail::class);
    }

    /**
     * Get the tracking URL for the work order.
     */
    public function getTrackingUrl(): string
    {
        return url("/track/{$this->tracking_token}");
    }

    /**
     * Generate QR code for the tracking URL.
     */
    public function getQrCode(int $size = 200): string
    {
        return app(QrCodeGenerator::class)->generate($this->getTrackingUrl(), $size);
    }

    /**
     * Get all public history items for customer tracking view.
     */
    public function getPublicHistoryItems()
    {
        return $this->history()
            ->where('is_public', true)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Check if notifications are enabled for a given channel.
     */
    public function isNotificationEnabled(string $channel): bool
    {
        $preferences = $this->notification_preferences ?? [];
        return !isset($preferences[$channel]) || $preferences[$channel] === true;
    }

    /**
     * Update notification preferences for a channel.
     */
    public function updateNotificationPreference(string $channel, bool $enabled): self
    {
        $preferences = $this->notification_preferences ?? [];
        $preferences[$channel] = $enabled;
        $this->notification_preferences = $preferences;
        $this->save();

        return $this;
    }

    /**
     * Get all available notification channels for this work order.
     */
    public function getAvailableNotificationChannels(): array
    {
        $availableChannels = [];

        // Email is always available if customer has email
        if (!empty($this->customer->email)) {
            $availableChannels['email'] = 'Email';
        }

        // Phone-based notifications
        if (!empty($this->customer->phone)) {
            $availableChannels['sms'] = 'SMS';
            $availableChannels['whatsapp'] = 'WhatsApp';
            $availableChannels['viber'] = 'Viber';
        }

        return $availableChannels;
    }
}
