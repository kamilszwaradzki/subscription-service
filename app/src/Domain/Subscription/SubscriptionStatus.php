<?php

declare(strict_types=1);

namespace App\Domain\Subscription;

enum SubscriptionStatus: string
{
    case PENDING_ACTIVATION = 'pending_activation';
    case ACTIVE = 'active';
    case GRACE_PERIOD = 'grace_period';
    case EXPIRED = 'expired';
    case CANCELED = 'canceled';
    case PAYMENT_FAILED = 'payment_failed';
    case PAYMENT_FAILED_PERMANENTLY = 'payment_failed_permanently';
}
