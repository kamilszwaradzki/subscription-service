<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Application\Subscription\Command\SucceedSubscriptionPaymentCommand;
use App\Application\Subscription\Command\SucceedSubscriptionPaymentHandler;
use App\Domain\Subscription\Subscription;
use App\Domain\ValueObject\PlanId;
use App\Domain\ValueObject\SubscriptionId;
use App\Domain\ValueObject\UserId;
use App\Infrastructure\Subscription\Repository\InMemorySubscriptionRepository;
use DateTimeImmutable;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class WebhookPaymentSuccessTest extends WebTestCase
{
    private $entityManager;
    private SubscriptionId $subscriptionId;

    protected function setUp(): void
    {
        self::bootKernel();

        $this->subscriptionId = SubscriptionId::generate();
        $subscription = Subscription::create(
            $this->subscriptionId,
            UserId::generate(),
            PlanId::generate(),
            new \DateTimeImmutable(),
            new \DateTimeImmutable('+1 month')
        );

        // Dodanie do repo w pamięci
        $repo = self::getContainer()->get(InMemorySubscriptionRepository::class);
        $repo->add($subscription);

    }

    public function testPaymentSuccessHandler(): void
    {
        // Arrange
        $handler = self::getContainer()->get(SucceedSubscriptionPaymentHandler::class);
        $command = new SucceedSubscriptionPaymentCommand($this->subscriptionId);

        // Act
        $handler($command);

        $repo = self::getContainer()->get(InMemorySubscriptionRepository::class);

        // Assert
        $subscription = $repo
            ->get($this->subscriptionId);

        $this->assertNotNull($subscription);
        $this->assertTrue($subscription->isPaymentSucceeded());
    }
}
