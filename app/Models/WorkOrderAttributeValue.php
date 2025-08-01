<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkOrderAttributeValue extends Model
{
    use HasFactory;

    protected $fillable = [
        'work_order_id',
        'work_category_attribute_id',
        'value',
    ];

    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(WorkOrder::class);
    }

    public function attribute(): BelongsTo
    {
        return $this->belongsTo(WorkCategoryAttribute::class, 'work_category_attribute_id');
    }
}
