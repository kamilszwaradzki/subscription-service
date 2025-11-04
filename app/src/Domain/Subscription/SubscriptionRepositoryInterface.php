<?php

namespace App\Domain\Subscription;

use App\Domain\ValueObject\SubscriptionId;

interface SubscriptionRepositoryInterface
{
    public function save(Subscription $subscription): void;

    public function get(SubscriptionId $id): Subscription;
}
