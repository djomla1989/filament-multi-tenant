<?php

namespace App\Models;

use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkCategoryAttribute extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'description',
        'options',
        'is_required',
        'is_active',
        'order',
        'work_category_id',
        'organization_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_required' => 'boolean',
        'options' => 'array',
        'order' => 'integer',
    ];

    public function workCategory(): BelongsTo
    {
        return $this->belongsTo(WorkCategory::class);
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function attributeValues(): HasMany
    {
        return $this->hasMany(WorkOrderAttributeValue::class);
    }

    public static function getTypes(): array
    {
        return [
            'text' => 'Text',
            'textarea' => 'Textarea',
            'number' => 'Number',
            'select' => 'Select',
            'radio' => 'Radio Buttons',
            'date' => 'Date',
            'checkbox' => 'Checkbox',
        ];
    }

    protected static function booted(): void
    {
        static::addGlobalScope('organization', function (Builder $query) {
            if (auth()->hasUser()) {
                $query->where('organization_id', Filament::getTenant()->id);
            }
        });
    }
}
