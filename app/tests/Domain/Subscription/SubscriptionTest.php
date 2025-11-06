<?php

declare(strict_types=1);

namespace Tests\Domain\Subscription;

use App\Domain\Subscription\Subscription;
use App\Domain\Subscription\SubscriptionStatus;
use App\Domain\ValueObject\SubscriptionId;
use App\Domain\ValueObject\UserId;
use App\Domain\ValueObject\PlanId;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

class SubscriptionTest extends TestCase
{
    public function test_create_subscription(): void
    {
        $id = SubscriptionId::generate();
        $userId = UserId::generate();
        $planId = PlanId::generate();
        $startDate = new DateTimeImmutable();
        $endDate = $startDate->modify('+1 month');

        $subscription = Subscription::create($id, $userId, $planId, $startDate, $endDate);

        $this->assertEquals($id, $subscription->getId());
        $this->assertEquals(SubscriptionStatus::PENDING_ACTIVATION, $subscription->getStatus());
        $this->assertCount(1, $subscription->getDomainEvents());
    }

    public function test_activate_pending_subscription(): void
    {
        $subscription = $this->createPendingSubscription();
        
        $subscription->activate();

        $this->assertEquals(SubscriptionStatus::ACTIVE, $subscription->getStatus());
        $this->assertCount(2, $subscription->getDomainEvents()); // Created + Activated
    }

    public function test_cannot_activate_non_pending_subscription(): void
    {
        $this->expectException(\LogicException::class);
        
        $subscription = $this->createActiveSubscription();
        $subscription->activate();
    }

    public function test_fail_payment_increments_counter(): void
    {
        $subscription = $this->createActiveSubscription();
        $initialAttempts = $subscription->getFailedAttemptsCount();

        $subscription->failPayment();

        $this->assertEquals($initialAttempts + 1, $subscription->getFailedAttemptsCount());
    }

    public function test_renew_only_works_when_active(): void
    {
        $subscription = $this->createActiveSubscription();
        $subscription->failPayment();

        $this->expectException(\LogicException::class);
        $subscription->renew(new DateTimeImmutable('+2 months'));
    }

    private function createPendingSubscription(): Subscription
    {
        return Subscription::create(
            SubscriptionId::generate(),
            UserId::generate(),
            PlanId::generate(),
            new DateTimeImmutable(),
            new DateTimeImmutable('+1 month')
        );
    }

    private function createActiveSubscription(): Subscription
    {
        $subscription = $this->createPendingSubscription();
        $subscription->activate();
        $subscription->clearDomainEvents();
        return $subscription;
    }
}