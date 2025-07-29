<?php

namespace App\Enums\Stripe\Refunds;

use Filament\Support\Contracts\{HasColor, HasLabel};

enum RefundSubscriptionEnum: string implements HasLabel, HasColor
{
    case DUPLICATE                 = 'duplicate';
    case FRAUDULENT                = 'fraudulent';
    case REQUESTED_BY_CUSTOMER     = 'requested_by_customer';
    case EXPIRED_UNCAPTURED_CHARGE = 'expired_uncaptured_charge';

    public function getLabel(): string
    {
        return match ($this) {
            self::DUPLICATE                 => 'Duplicate',
            self::FRAUDULENT                => 'Fraudulent',
            self::REQUESTED_BY_CUSTOMER     => 'Requested by Customer',
            self::EXPIRED_UNCAPTURED_CHARGE => 'Expired Charge',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::DUPLICATE                 => 'success',
            self::FRAUDULENT                => 'success',
            self::REQUESTED_BY_CUSTOMER     => 'success',
            self::EXPIRED_UNCAPTURED_CHARGE => 'success',
        };
    }
}
