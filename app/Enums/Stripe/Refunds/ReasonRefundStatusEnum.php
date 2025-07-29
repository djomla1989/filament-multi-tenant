<?php

namespace App\Enums\Stripe\Refunds;

use Filament\Support\Contracts\HasLabel;

enum ReasonRefundStatusEnum: string implements HasLabel
{
    case CHARGE_FOR_PENDING_REFUND_DISPUTED = 'charge_for_pending_refund_disputed';
    case DECLINED                           = 'declined';
    case EXPIRED_OR_CANCELED_CARD           = 'expired_or_canceled_card';
    case INSUFFICIENT_FUNDS                 = 'insufficient_funds';
    case LOST_OR_STOLEN_CARD                = 'lost_or_stolen_card';
    case MERCANT_REQUEST                    = 'merchant_request';
    case UNKNOWN                            = 'unknown';

    public function getLabel(): string
    {
        return match ($this) {
            self::CHARGE_FOR_PENDING_REFUND_DISPUTED => 'Refund dispute',
            self::DECLINED                           => 'Refund declined',
            self::EXPIRED_OR_CANCELED_CARD           => 'Expired or Cancelled',
            self::INSUFFICIENT_FUNDS                 => 'Insufficient funds',
            self::LOST_OR_STOLEN_CARD                => 'Lost or stolen card',
            self::MERCANT_REQUEST                    => 'Refund failure',
            self::UNKNOWN                            => 'Failed for unknown reason',
        };
    }

}
