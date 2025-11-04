<?php

namespace App\Application\Subscription\Command;

use App\Domain\ValueObject\SubscriptionId;

final class ActivateSubscription
{
    public function __construct(public SubscriptionId $id) {}
}
