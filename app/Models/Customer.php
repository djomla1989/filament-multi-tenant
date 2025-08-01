<?php

namespace App\Models;

use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'organization_id',
        'user_id',
    ];

    /**
     * Get the user that owns the customer record.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the organization that owns the customer.
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Get the work orders for the customer.
     */
    public function workOrders(): HasMany
    {
        return $this->hasMany(WorkOrder::class);
    }

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
    }
}
