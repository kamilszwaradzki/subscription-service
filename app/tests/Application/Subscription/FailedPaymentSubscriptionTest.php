<?php

declare(strict_types=1);

namespace Tests\Application\Subscription;

use App\Application\Subscription\Command\ActivateSubscriptionCommand;
use App\Application\Subscription\Command\ActivateSubscriptionHandler;
use App\Application\Subscription\Command\FailSubscriptionPaymentCommand;
use App\Application\Subscription\Command\FailSubscriptionPaymentHandler;
use App\Infrastructure\Subscription\Repository\InMemorySubscriptionRepository;
use App\Domain\Subscription\Subscription;
use App\Domain\Subscription\SubscriptionStatus;
use App\Domain\ValueObject\SubscriptionId;
use App\Domain\ValueObject\UserId;
use App\Domain\ValueObject\PlanId;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Handler\HandlersLocator;
use Symfony\Component\Messenger\MessageBus;
use Symfony\Component\Messenger\Middleware\HandleMessageMiddleware;

class FailedPaymentSubscriptionTest extends TestCase
{
    public function test_it_sets_subscription_to_permanent_failure_after_three_failed_payments(): void
    {
        $repo = new InMemorySubscriptionRepository();
        $handler = new FailSubscriptionPaymentHandler($repo);

        $id = SubscriptionId::generate();
        $subscription = Subscription::create(
            $id,
            UserId::generate(),
            PlanId::generate(),
            new DateTimeImmutable(),
            new DateTimeImmutable('+1 month')
        );

        $repo->add($subscription);

        $handlerActiv = new ActivateSubscriptionHandler($repo);
        $handlerActiv(new ActivateSubscriptionCommand($id));
        $this->assertEquals(SubscriptionStatus::ACTIVE, $subscription->getStatus());

        $handler(new FailSubscriptionPaymentCommand($subscription->getId()));
        $this->assertEquals(SubscriptionStatus::PAYMENT_FAILED, $subscription->getStatus());
        $handler(new FailSubscriptionPaymentCommand($subscription->getId()));
        $this->assertEquals(SubscriptionStatus::PAYMENT_FAILED, $subscription->getStatus());
        $handler(new FailSubscriptionPaymentCommand($subscription->getId()));
        $this->assertEquals(SubscriptionStatus::PAYMENT_FAILED, $subscription->getStatus());
        $handler(new FailSubscriptionPaymentCommand($subscription->getId()));
        $this->assertEquals(SubscriptionStatus::PAYMENT_FAILED_PERMANENTLY, $subscription->getStatus());

        $loaded = $repo->get($id);
        $this->assertEquals(4, $loaded->getFailedAttemptsCount());

        $this->assertEquals(SubscriptionStatus::PAYMENT_FAILED_PERMANENTLY, $loaded->getStatus());
    }
    
}
