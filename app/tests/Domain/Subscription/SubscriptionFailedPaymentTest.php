<?php

declare(strict_types=1);

namespace Tests\Domain\Subscription;

use PHPUnit\Framework\TestCase;
use App\Domain\Subscription\Subscription;
use App\Domain\Subscription\SubscriptionRepositoryInterface;
use App\Domain\ValueObject\PlanId;
use App\Domain\ValueObject\SubscriptionId;
use App\Domain\ValueObject\UserId;

final class SubscriptionFailedPaymentTest extends TestCase
{
    public function testThreeFailedPaymentsDoesNotLock(): void
    {
        $subscription = Subscription::create(
            SubscriptionId::generate(),
            UserId::generate(),
            PlanId::generate(),
            new \DateTimeImmutable('-1 day'),
            new \DateTimeImmutable('+29 days')
        );

        $subscription->failPayment();
        $this->assertFalse($subscription->isLocked());

        $subscription->failPayment();
        $this->assertFalse($subscription->isLocked());

        $subscription->failPayment();
        $this->assertFalse($subscription->isLocked());
        $this->assertEquals(3, $subscription->getFailedAttemptsCount());
    }

    public function testFourFailedPaymentsLocksSubscription(): void
    {
        $repo = $this->createMock(SubscriptionRepositoryInterface::class);
        $subscription = Subscription::create(
            SubscriptionId::generate(),
            UserId::generate(),
            PlanId::generate(),
            new \DateTimeImmutable('-1 day'),
            new \DateTimeImmutable('+29 days')
        );

        $subscription->failPayment();
        $this->assertFalse($subscription->isLocked());

        $subscription->failPayment();
        $this->assertFalse($subscription->isLocked());

        $subscription->failPayment();
        $this->assertFalse($subscription->isLocked());

        $subscription->failPayment();
        $this->assertEquals(4, $subscription->getFailedAttemptsCount());
        $this->assertTrue($subscription->isLocked());
    }
}
