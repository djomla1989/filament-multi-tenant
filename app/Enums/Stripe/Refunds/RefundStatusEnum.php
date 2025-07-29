<?php

namespace App\Enums\Stripe\Refunds;

use Filament\Support\Contracts\{HasColor, HasLabel};

enum RefundStatusEnum: string implements HasLabel, HasColor
{
    case PENDING         = 'pending';
    case REQUIRES_ACTION = 'requires_action';
    case SUCCEEDED       = 'succeeded';
    case FAILED          = 'failed';
    case CANCELED        = 'canceled';

    public function getLabel(): string
    {
        return match ($this) {
            self::PENDING         => 'Pending',
            self::REQUIRES_ACTION => 'Requires Action',
            self::SUCCEEDED       => 'Succeeded',
            self::FAILED          => 'Failed',
            self::CANCELED        => 'Canceled',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::PENDING         => 'prymary',
            self::REQUIRES_ACTION => 'warning',
            self::SUCCEEDED       => 'success',
            self::FAILED          => 'danger',
            self::CANCELED        => 'danger',
        };
    }
}
