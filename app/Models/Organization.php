<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsToMany, HasMany};
use Laravel\Cashier\Billable;

class Organization extends Model
{
    use HasFactory;
    use Billable;

    protected $fillable = [
        'name',
        'document_number',
        'stripe_id',
        'email',
        'phone',
        'address',
        'address_number',
        'city',
        'zip_code',
        'country',
        'slug',
        'pm_type',
        'pm_last_four',
        'card_exp_month',
        'card_exp_year',
        'card_country',
    ];

    /**
    * @return BelongsToMany<User, $this>
    */

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'organization_user', 'organization_id', 'user_id');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function subscription_refunds(): HasMany
    {
        return $this->hasMany(SubscriptionRefund::class);
    }

    public function whatsappInstances(): HasMany
    {
        return $this->hasMany(WhatsappInstance::class);
    }

    public function workCategories(): HasMany
    {
        return $this->hasMany(WorkCategory::class);
    }

    public function workCategoryStatuses(): HasMany
    {
        return $this->hasMany(WorkCategoryStatus::class);
    }

    public function workCategoryAttributes(): HasMany
    {
        return $this->hasMany(WorkCategoryAttribute::class);
    }

    public function workOrders(): HasMany
    {
        return $this->hasMany(WorkOrder::class);
    }

}
