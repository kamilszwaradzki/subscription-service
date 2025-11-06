<?php

namespace App\Application\Subscription\Command;

use App\Domain\ValueObject\SubscriptionId;

final class SucceedSubscriptionPaymentCommand
{
    public function __construct(public readonly SubscriptionId $id) {}
}
