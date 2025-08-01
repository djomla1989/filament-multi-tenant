<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkOrderDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'work_order_id',
        'key',
        'value',
    ];

    /**
     * Get the work order that owns the detail entry.
     */
    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(WorkOrder::class);
    }
}
