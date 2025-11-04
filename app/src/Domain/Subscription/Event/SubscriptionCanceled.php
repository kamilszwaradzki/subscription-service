<?php

declare(strict_types=1);

namespace App\Domain\Subscription\Event;

use App\Domain\ValueObject\SubscriptionId;
use DateTimeImmutable;

final class SubscriptionCanceled implements DomainEvent
{
    public function __construct(
        private readonly SubscriptionId $subscriptionId,
        private readonly bool $refundProcessed = false,
        private readonly DateTimeImmutable $occurredAt = new DateTimeImmutable()
    ) {}

    public function subscriptionId(): SubscriptionId { return $this->subscriptionId; }
    public function refundProcessed(): bool { return $this->refundProcessed; }
    public function occurredAt(): DateTimeImmutable { return $this->occurredAt; }
}
