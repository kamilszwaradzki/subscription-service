<?php

declare(strict_types=1);

namespace App\Domain\Subscription\Event;

use App\Domain\ValueObject\SubscriptionId;
use App\Domain\ValueObject\UserId;
use App\Domain\ValueObject\PlanId;
use DateTimeImmutable;

final class SubscriptionCreated implements DomainEvent
{
    public function __construct(
        private readonly SubscriptionId $subscriptionId,
        private readonly UserId $userId,
        private readonly PlanId $planId,
        private readonly DateTimeImmutable $occurredAt = new DateTimeImmutable()
    ) {}

    public function subscriptionId(): SubscriptionId { return $this->subscriptionId; }
    public function userId(): UserId { return $this->userId; }
    public function planId(): PlanId { return $this->planId; }
    public function occurredAt(): DateTimeImmutable { return $this->occurredAt; }
}
