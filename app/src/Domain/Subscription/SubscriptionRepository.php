<?php

declare(strict_types=1);

namespace App\Domain\Subscription;

use App\Domain\ValueObject\SubscriptionId;
use App\Domain\ValueObject\UserId;

interface SubscriptionRepository
{
    public function get(SubscriptionId $id): Subscription;
    public function add(Subscription $subscription): void;
    public function update(Subscription $subscription): void;
    public function findByUserId(UserId $userId): ?Subscription;
    public function findDueForRenewal(\DateTimeImmutable $currentTime): array;
    public function findExpiringGracePeriods(\DateTimeImmutable $currentTime): array;
}

