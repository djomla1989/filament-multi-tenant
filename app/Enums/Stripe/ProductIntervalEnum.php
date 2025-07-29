<?php

namespace App\Enums\Stripe;

use Filament\Support\Contracts\{HasColor, HasDescription, HasLabel};

enum ProductIntervalEnum: string implements HasLabel, HasColor, HasDescription
{
    case YEAR  = 'year';
    case MONTH = 'month';
    case WEEK  = 'week';
    case DAY   = 'day';

    public function getLabel(): string
    {
        return match ($this) {
            self::YEAR  => 'Annual',
            self::MONTH => 'Monthly',
            self::WEEK  => 'Weekly',
            self::DAY   => 'Daily',
        };
    }
    public function getColor(): string|array|null
    {
        return match ($this) {
            self::YEAR  => 'success',
            self::MONTH => 'success',
            self::WEEK  => 'success',
            self::DAY   => 'success',
        };
    }

    public function getDescription(): string
    {
        return match ($this) {
            self::YEAR  => 'Year',
            self::MONTH => 'Month',
            self::WEEK  => 'Week',
            self::DAY   => 'Day',
        };
    }
}
