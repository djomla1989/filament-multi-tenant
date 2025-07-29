<?php

namespace App\Enums\Stripe;

use Filament\Support\Contracts\{HasColor, HasDescription, HasLabel};

enum CancelSubscriptionEnum: string implements HasLabel, HasColor, HasDescription
{
    case CUSTUMER_SERVICE = 'customer_service';
    case LOW_QUALITY      = 'low_quality';
    case MISSING_FEATURES = 'missing_features';
    case SWITCHED_SERVICE = 'switched_service';
    case TOO_COMPLEX      = 'too_complex';
    case TOO_EXPENSIVE    = 'too_expensive';
    case UNUSED           = 'unused';

    public function getLabel(): string
    {
        return match ($this) {
            self::CUSTUMER_SERVICE => 'Poor Service',
            self::LOW_QUALITY      => 'Low Quality',
            self::MISSING_FEATURES => 'Missing Features',
            self::SWITCHED_SERVICE => 'Switching Provider',
            self::TOO_COMPLEX      => 'Too Complex',
            self::TOO_EXPENSIVE    => 'Too Expensive',
            self::UNUSED           => 'Not Used',
        };
    }
    public function getColor(): string|array|null
    {
        return match ($this) {
            self::CUSTUMER_SERVICE => 'success',
            self::LOW_QUALITY      => 'success',
            self::MISSING_FEATURES => 'success',
            self::SWITCHED_SERVICE => 'success',
            self::TOO_COMPLEX      => 'success',
            self::TOO_EXPENSIVE    => 'success',
            self::UNUSED           => 'success',
        };
    }

    public function getDescription(): string
    {
        return match ($this) {
            self::CUSTUMER_SERVICE => 'Customer service was below expectations',
            self::LOW_QUALITY      => 'Quality was below expectations',
            self::MISSING_FEATURES => 'Some features are missing',
            self::SWITCHED_SERVICE => 'Switching to a different service',
            self::TOO_COMPLEX      => 'Ease of use was below expectations',
            self::TOO_EXPENSIVE    => 'It is too expensive',
            self::UNUSED           => 'I do not use the service enough',

        };
    }
}
