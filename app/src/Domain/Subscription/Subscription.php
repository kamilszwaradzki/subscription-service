<?php

declare(strict_types=1);

namespace App\Domain\Subscription;

use App\Domain\ValueObject\SubscriptionId;
use App\Domain\ValueObject\UserId;
use App\Domain\ValueObject\PlanId;
use App\Domain\Subscription\Event\SubscriptionActivated;
use App\Domain\Subscription\Event\SubscriptionCanceled;
use App\Domain\Subscription\Event\SubscriptionCreated;
use App\Domain\Subscription\Event\SubscriptionExpired;
use App\Domain\Subscription\Event\SubscriptionGracePeriodStarted;
use App\Domain\Subscription\Event\SubscriptionPaymentFailed;
use App\Domain\Subscription\Event\SubscriptionRenewed;
use DateTimeImmutable;

final class Subscription
{
    private array $domainEvents = [];

    private function __construct(
        private SubscriptionId $id,
        private UserId $userId,
        private PlanId $planId,
        private SubscriptionStatus $status,
        private DateTimeImmutable $startDate,
        private DateTimeImmutable $endDate,
        private int $failedAttemptsCount,
        private ?DateTimeImmutable $graceUntil,
    ) {}

    public static function create(
        SubscriptionId $id,
        UserId $userId,
        PlanId $planId,
        DateTimeImmutable $startDate,
        DateTimeImmutable $endDate
    ): self {
        $subscription = new self(
            $id,
            $userId,
            $planId,
            SubscriptionStatus::PENDING_ACTIVATION,
            $startDate,
            $endDate,
            0,
            null
        );

        $subscription->recordEvent(new SubscriptionCreated($id, $userId, $planId));
        return $subscription;
    }

    public function activate(): void
    {
        if ($this->status !== SubscriptionStatus::PENDING_ACTIVATION) {
            throw new \LogicException('Cannot activate unless pending.');
        }

        $this->status = SubscriptionStatus::ACTIVE;
        $this->recordEvent(new SubscriptionActivated($this->id));
    }

    public function failPayment(): void
    {
        if ($this->status !== SubscriptionStatus::ACTIVE) {
            throw new \LogicException('Payment fail only applies when active.');
        }

        $this->failedAttemptsCount++;
        $this->recordEvent(new SubscriptionPaymentFailed($this->id, $this->failedAttemptsCount));
    }

    public function enterGracePeriod(DateTimeImmutable $until): void
    {
        if ($this->status !== SubscriptionStatus::ACTIVE) {
            throw new \LogicException('Grace only from active.');
        }

        $this->status = SubscriptionStatus::GRACE_PERIOD;
        $this->graceUntil = $until;
        $this->recordEvent(new SubscriptionGracePeriodStarted($this->id, $until));
    }

    public function expire(): void
    {
        if (!in_array($this->status, [SubscriptionStatus::GRACE_PERIOD, SubscriptionStatus::ACTIVE], true)) {
            throw new \LogicException('Expire only after grace or active.');
        }

        $this->status = SubscriptionStatus::EXPIRED;
        $this->recordEvent(new SubscriptionExpired($this->id));
    }

    public function cancel(): void
    {
        if ($this->status === SubscriptionStatus::EXPIRED) {
            throw new \LogicException('Cannot cancel expired.');
        }

        $this->status = SubscriptionStatus::CANCELED;
        $this->recordEvent(new SubscriptionCanceled($this->id));
    }

    public function renew(DateTimeImmutable $newEndDate): void
    {
        if ($this->status !== SubscriptionStatus::ACTIVE) {
            throw new \LogicException('Can only renew active subscription.');
        }

        $this->endDate = $newEndDate;
        $this->failedAttemptsCount = 0;
        $this->recordEvent(new SubscriptionRenewed($this->id, $newEndDate));
    }

    public function getDomainEvents(): array
    {
        return $this->domainEvents;
    }

    public function clearDomainEvents(): void
    {
        $this->domainEvents = [];
    }

    private function recordEvent(object $event): void
    {
        $this->domainEvents[] = $event;
    }
    public function getId(): SubscriptionId
    {
        return $this->id;
    }

    public function getStatus(): SubscriptionStatus
    {
        return $this->status;
    }

    public function getEndDate(): DateTimeImmutable
    {
        return $this->endDate;
    }

    public function getFailedAttemptsCount(): int
    {
        return $this->failedAttemptsCount;
    }
}