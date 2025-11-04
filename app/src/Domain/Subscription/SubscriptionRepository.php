<?php

declare(strict_types=1);

namespace App\Domain\Subscription;

use App\Domain\ValueObject\SubscriptionId;

interface SubscriptionRepository
{
    public function save(Subscription $subscription): void;

    public function getById(SubscriptionId $id): Subscription;
}
