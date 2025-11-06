<?php

namespace App\Application\Subscription\Command;

use App\Domain\ValueObject\SubscriptionId;

class FailSubscriptionPaymentCommand
{
    public function __construct(
        public readonly SubscriptionId $id
    ) {}
}
