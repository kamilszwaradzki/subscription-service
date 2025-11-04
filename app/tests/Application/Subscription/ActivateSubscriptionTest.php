<?php

declare(strict_types=1);

namespace Tests\Application\Subscription;

use App\Application\Subscription\Command\ActivateSubscriptionCommand;
use App\Application\Subscription\Command\ActivateSubscriptionHandler;
use App\Infrastructure\Subscription\Repository\InMemorySubscriptionRepository;
use App\Domain\Subscription\Subscription;
use App\Domain\Subscription\SubscriptionStatus;
use App\Domain\ValueObject\SubscriptionId;
use App\Domain\ValueObject\UserId;
use App\Domain\ValueObject\PlanId;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

class ActivateSubscriptionTest extends TestCase
{
    public function test_it_activates_a_subscription(): void
    {
        $repo = new InMemorySubscriptionRepository();
        $handler = new ActivateSubscriptionHandler($repo);

        $id = SubscriptionId::generate();
        $subscription = Subscription::create(
            $id,
            UserId::generate(),
            PlanId::generate(),
            new DateTimeImmutable(),
            new DateTimeImmutable('+1 month')
        );

        $repo->save($subscription);

        $handler(new ActivateSubscriptionCommand($id));

        $loaded = $repo->getById($id);

        $this->assertEquals(SubscriptionStatus::ACTIVE, $loaded->getStatus());
    }
}
