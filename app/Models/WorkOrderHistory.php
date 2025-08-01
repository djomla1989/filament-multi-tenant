<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkOrderHistory extends Model
{
    use HasFactory;

    protected $table = 'work_order_history';

    protected $fillable = [
        'work_order_id',
        'status_id',
        'notes',
        'is_public',
        'created_by_id',
    ];

    protected $casts = [
        'is_public' => 'boolean',
    ];

    /**
     * Get the work order that owns the history entry.
     */
    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(WorkOrder::class);
    }

    /**
     * Get the status of this history entry.
     */
    public function status(): BelongsTo
    {
        return $this->belongsTo(WorkCategoryStatus::class, 'status_id');
    }

    /**
     * Get the user who created the history entry.
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }
}
