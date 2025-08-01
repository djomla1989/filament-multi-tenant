<?php

namespace App\Services\WorkOrder;

use App\Models\WorkCategory;
use App\Models\WorkCategoryAttribute;
use App\Models\WorkOrder;
use App\Models\WorkOrderAttributeValue;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Illuminate\Support\Collection;

class DynamicAttributesService
{
    /**
     * Get dynamic form fields for the given work category
     */
    public function getDynamicFormFields(int $categoryId = null): array
    {
        if (!$categoryId) {
            return [];
        }

        $attributes = WorkCategoryAttribute::where('work_category_id', $categoryId)
            ->where('is_active', true)
            ->orderBy('order')
            ->get();

        if ($attributes->isEmpty()) {
            return [];
        }

        $fields = [];

        foreach ($attributes as $attribute) {
            $fields[] = $this->createFormField($attribute);
        }

        return $fields;
    }

    /**
     * Create a form field based on the attribute type
     */
    protected function createFormField(WorkCategoryAttribute $attribute)
    {
        $fieldName = "attribute_values.{$attribute->id}";
        $isRequired = $attribute->is_required;

        return match ($attribute->type) {
            'text' => TextInput::make($fieldName)
                ->label($attribute->name)
                ->required($isRequired)
                ->maxLength(255)
                ->helperText($attribute->description),

            'textarea' => Textarea::make($fieldName)
                ->label($attribute->name)
                ->required($isRequired)
                ->rows(3)
                ->helperText($attribute->description),

            'number' => TextInput::make($fieldName)
                ->label($attribute->name)
                ->required($isRequired)
                ->numeric()
                ->helperText($attribute->description),

            'select' => Select::make($fieldName)
                ->label($attribute->name)
                ->required($isRequired)
                ->options($this->formatOptions($attribute->options))
                ->helperText($attribute->description),

            'radio' => Radio::make($fieldName)
                ->label($attribute->name)
                ->required($isRequired)
                ->options($this->formatOptions($attribute->options))
                ->helperText($attribute->description),

            'date' => DatePicker::make($fieldName)
                ->label($attribute->name)
                ->required($isRequired)
                ->helperText($attribute->description),

            'checkbox' => Checkbox::make($fieldName)
                ->label($attribute->name)
                ->helperText($attribute->description),

            default => TextInput::make($fieldName)
                ->label($attribute->name)
                ->required($isRequired)
                ->helperText($attribute->description),
        };
    }

    /**
     * Format options array for select/radio fields
     */
    protected function formatOptions(?array $options): array
    {
        if (!$options) {
            return [];
        }

        $formattedOptions = [];

        foreach ($options as $option) {
            if (isset($option['value']) && isset($option['label'])) {
                $formattedOptions[$option['value']] = $option['label'];
            }
        }

        return $formattedOptions;
    }

    /**
     * Save attribute values from the form data
     */
    public function saveAttributeValues(WorkOrder $workOrder, array $formData): void
    {
        if (!isset($formData['attribute_values']) || !is_array($formData['attribute_values'])) {
            return;
        }

        $attributeValues = $formData['attribute_values'];

        foreach ($attributeValues as $attributeId => $value) {
            WorkOrderAttributeValue::updateOrCreate(
                [
                    'work_order_id' => $workOrder->id,
                    'work_category_attribute_id' => $attributeId,
                ],
                ['value' => is_array($value) ? json_encode($value) : $value]
            );
        }
    }

    /**
     * Get attribute values for a work order
     */
    public function getAttributeValuesForForm(WorkOrder $workOrder): array
    {
        $values = [];

        $attributeValues = $workOrder->attributeValues()->with('attribute')->get();

        foreach ($attributeValues as $attributeValue) {
            $values[$attributeValue->work_category_attribute_id] = $attributeValue->value;
        }

        return $values;
    }
}
