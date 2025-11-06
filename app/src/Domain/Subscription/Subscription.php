<?php

declare(strict_types=1);

namespace App\Domain\Subscription;

use Doctrine\ORM\Mapping as ORM;
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
use App\Domain\Subscription\Event\SubscriptionTerminated;
use DateTimeImmutable;

#[ORM\Entity]
#[ORM\Table(name: 'subscriptions')]
final class Subscription
{
    #[ORM\Id]
    #[ORM\Column(type: 'subscription_id', length: 36)]
    private string $id;

    #[ORM\Column(type: 'user_id', length: 36)]
    private string $userId;

    #[ORM\Column(type: 'plan_id', length: 36)]
    private string $planId;

    #[ORM\Column(type: 'string', length: 20)]
    private string $status;

    #[ORM\Column(type: 'datetime_immutable')]
    private DateTimeImmutable $startDate;

    #[ORM\Column(type: 'datetime_immutable')]
    private DateTimeImmutable $endDate;

    #[ORM\Column(type: 'integer')]
    private int $failedAttemptsCount = 0;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?DateTimeImmutable $graceUntil = null;

    private array $domainEvents = [];

    protected function __construct() {}

    public static function create(
        SubscriptionId $id,
        UserId $userId,
        PlanId $planId,
        DateTimeImmutable $startDate,
        DateTimeImmutable $endDate
    ): self {
        $self = new self();
        $self->id = (string) $id;
        $self->userId = (string) $userId;
        $self->planId = (string) $planId;
        $self->status = SubscriptionStatus::PENDING_ACTIVATION->value;
        $self->startDate = $startDate;
        $self->endDate = $endDate;
        $self->failedAttemptsCount = 0;

        $self->recordEvent(new SubscriptionCreated($id, $userId, $planId));

        return $self;
    }

    public function activate(): void
    {
        if ($this->status !== SubscriptionStatus::PENDING_ACTIVATION->value) {
            throw new \LogicException('Cannot activate unless pending.');
        }

        $this->status = SubscriptionStatus::ACTIVE->value;
        $this->recordEvent(new SubscriptionActivated($this->getId()));
    }

    public function failPayment(): void
    {
        $this->failedAttemptsCount++;
        if ($this->failedAttemptsCount > 3) {
            $this->status = SubscriptionStatus::PAYMENT_FAILED_PERMANENTLY->value;
            $this->recordEvent(new SubscriptionTerminated($this->getId(), $this->status));
        } else {
            $this->status = SubscriptionStatus::PAYMENT_FAILED->value;
            $this->recordEvent(new SubscriptionPaymentFailed($this->getId(), $this->failedAttemptsCount));
        }
    }

    public function enterGracePeriod(DateTimeImmutable $until): void
    {
        if ($this->status !== SubscriptionStatus::ACTIVE->value) {
            throw new \LogicException('Grace only from active.');
        }

        $this->status = SubscriptionStatus::GRACE_PERIOD->value;
        $this->graceUntil = $until;
        $this->recordEvent(new SubscriptionGracePeriodStarted($this->getId(), $until));
    }

    public function expire(): void
    {
        if (!in_array($this->status, [
            SubscriptionStatus::GRACE_PERIOD->value,
            SubscriptionStatus::ACTIVE->value
        ], true)) {
            throw new \LogicException('Expire only after grace or active.');
        }

        $this->status = SubscriptionStatus::EXPIRED->value;
        $this->recordEvent(new SubscriptionExpired($this->getId()));
    }

    public function cancel(): void
    {
        if ($this->status === SubscriptionStatus::EXPIRED->value) {
            throw new \LogicException('Cannot cancel expired.');
        }

        $this->status = SubscriptionStatus::CANCELED->value;
        $this->recordEvent(new SubscriptionCanceled($this->getId()));
    }

    public function renew(DateTimeImmutable $newEndDate): void
    {
        if ($this->status !== SubscriptionStatus::ACTIVE->value) {
            throw new \LogicException('Can only renew active subscription.');
        }

        $this->endDate = $newEndDate;
        $this->failedAttemptsCount = 0;
        $this->recordEvent(new SubscriptionRenewed($this->getId(), $newEndDate));
    }

    private function recordEvent(object $event): void
    {
        $this->domainEvents[] = $event;
    }

    public function getId(): SubscriptionId
    {
        return SubscriptionId::fromString($this->id);
    }

    public function getStatus(): SubscriptionStatus
    {
        return SubscriptionStatus::from($this->status);
    }

    public function getEndDate(): DateTimeImmutable
    {
        return $this->endDate;
    }

    public function getFailedAttemptsCount(): int
    {
        return $this->failedAttemptsCount;
    }

    public function getDomainEvents(): array
    {
        return $this->domainEvents;
    }

    public function clearDomainEvents(): void
    {
        $this->domainEvents = [];
    }

    public function isLocked(): bool
    {
        return $this->getStatus() === SubscriptionStatus::PAYMENT_FAILED_PERMANENTLY;
    }

    public function markPaymentSucceeded(): void
    {
        $this->failedAttemptsCount = 0;
        $this->status = SubscriptionStatus::PENDING_ACTIVATION->value;
    }

    public function isPaymentSucceeded()
    {
        return $this->getStatus() === SubscriptionStatus::PENDING_ACTIVATION;
    }
}
