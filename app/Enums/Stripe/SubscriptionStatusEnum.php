<?php

namespace App\Enums\Stripe;

use Filament\Support\Contracts\{HasColor, HasLabel};

enum SubscriptionStatusEnum: string implements HasLabel, HasColor
{
    case INCOMPLETE         = 'incomplete';
    case TRIALING           = 'trialing';
    case ACTIVE             = 'active';
    case INCOMPLETE_EXPIRED = 'incomplete_expired';
    case PAST_DUE           = 'past_due';
    case UNPAID             = 'unpaid';
    case CANCELED           = 'canceled';
    case PAUSED             = 'paused';

    public function getLabel(): string
    {
        return match ($this) {
            self::INCOMPLETE         => 'Pending',
            self::TRIALING           => 'Trialing',
            self::ACTIVE             => 'Active',
            self::INCOMPLETE_EXPIRED => 'Canceled',
            self::PAST_DUE           => 'Awaiting Payment',
            self::UNPAID             => 'Card Declined',
            self::CANCELED           => 'Canceled',
            self::PAUSED             => 'Paused',

        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::INCOMPLETE         => 'warning',
            self::TRIALING           => 'gray',
            self::ACTIVE             => 'success',
            self::INCOMPLETE_EXPIRED => 'danger',
            self::PAST_DUE           => 'warning',
            self::UNPAID             => 'danger',
            self::CANCELED           => 'danger',
            self::PAUSED             => 'warning',

        };
    }
}
