<?php

declare(strict_types=1);

namespace App\Domain\Subscription\Event;

use App\Domain\ValueObject\SubscriptionId;
use DateTimeImmutable;

final class SubscriptionExpired implements DomainEvent
{
    public function __construct(
        private readonly SubscriptionId $subscriptionId,
        private readonly ?string $finalReason = null,
        private readonly DateTimeImmutable $occurredAt = new DateTimeImmutable()
    ) {}

    public function subscriptionId(): SubscriptionId { return $this->subscriptionId; }
    public function finalReason(): ?string { return $this->finalReason; }
    public function occurredAt(): DateTimeImmutable { return $this->occurredAt; }
}
