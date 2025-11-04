<?php

declare(strict_types=1);

namespace App\Infrastructure\Subscription\Repository;

use App\Domain\Subscription\Subscription;
use App\Domain\ValueObject\SubscriptionId;
use App\Domain\Subscription\SubscriptionRepository;

final class InMemorySubscriptionRepository implements SubscriptionRepository
{
    /** @var array<string, Subscription> */
    private array $items = [];

    public function save(Subscription $subscription): void
    {
        $this->items[(string)$subscription->getId()] = $subscription;
    }

    public function getById(SubscriptionId $id): Subscription
    {
        $key = (string)$id;

        if (!isset($this->items[$key])) {
            throw new \RuntimeException("Subscription not found: $key");
        }

        return $this->items[$key];
    }
}
