<?php

declare(strict_types=1);

namespace App\Domain\Subscription\Event;

use App\Domain\ValueObject\SubscriptionId;
use DateTimeImmutable;

final class SubscriptionRenewed implements DomainEvent
{
    public function __construct(
        private readonly SubscriptionId $subscriptionId,
        private readonly DateTimeImmutable $newEndDate,
        private readonly DateTimeImmutable $occurredAt = new DateTimeImmutable()
    ) {}

    public function subscriptionId(): SubscriptionId { return $this->subscriptionId; }
    public function newEndDate(): DateTimeImmutable { return $this->newEndDate; }
    public function occurredAt(): DateTimeImmutable { return $this->occurredAt; }
}
