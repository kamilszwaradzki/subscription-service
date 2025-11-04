<?php

declare(strict_types=1);

namespace App\Domain\Subscription\Event;

use App\Domain\ValueObject\SubscriptionId;
use DateTimeImmutable;

final class SubscriptionPaymentFailed implements DomainEvent
{
    public function __construct(
        private readonly SubscriptionId $subscriptionId,
        private readonly int $attemptNumber,
        private readonly ?string $reason = null,
        private readonly DateTimeImmutable $occurredAt = new DateTimeImmutable()
    ) {}

    public function subscriptionId(): SubscriptionId { return $this->subscriptionId; }
    public function attemptNumber(): int { return $this->attemptNumber; }
    public function reason(): ?string { return $this->reason; }
    public function occurredAt(): DateTimeImmutable { return $this->occurredAt; }
}
