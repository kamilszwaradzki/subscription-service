<?php

declare(strict_types=1);

namespace App\Infrastructure\Subscription\Repository;

use App\Domain\Subscription\Subscription;
use App\Domain\ValueObject\SubscriptionId;
use App\Domain\Subscription\SubscriptionRepository;
use App\Domain\ValueObject\UserId;

final class InMemorySubscriptionRepository implements SubscriptionRepository
{
    /** @var array<string, Subscription> */
    private array $items = [];

    public function add(Subscription $subscription): void
    {
        $this->items[(string)$subscription->getId()] = $subscription;
    }

    public function get(SubscriptionId $id): Subscription
    {
        $key = (string)$id;

        if (!isset($this->items[$key])) {
            throw new \RuntimeException("Subscription not found: $key");
        }

        return $this->items[$key];
    }

    public function update(Subscription $subscription): void
    {
        $key = (string)$subscription->getId();
        if (!isset($this->items[$key])) {
            throw new \RuntimeException("Subscription not found: $key");
        }
        $this->items[(string)$subscription->getId()] = $subscription;
    }

    public function findByUserId(UserId $userId): ?Subscription
    {
        throw new \RuntimeException("findByUserId method is not implemented.");
    }

    public function findDueForRenewal(\DateTimeImmutable $currentTime): array
    {
        throw new \RuntimeException("findDueForRenewal method is not implemented.");
    }

    public function findExpiringGracePeriods(\DateTimeImmutable $currentTime): array
    {
        throw new \RuntimeException("findExpiringGracePeriods method is not implemented.");
    }
}
