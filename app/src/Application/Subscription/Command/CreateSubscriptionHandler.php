<?php

namespace App\Application\Subscription\Command;

use App\Domain\Subscription\Subscription;
use App\Domain\Subscription\SubscriptionRepository;
use App\Domain\ValueObject\SubscriptionId;
use App\Domain\ValueObject\UserId;
use App\Domain\ValueObject\PlanId;

class CreateSubscriptionHandler
{
    public function __construct(private SubscriptionRepository $repo) {}

    public function __invoke(CreateSubscriptionCommand $cmd): void
    {
        $subscription = Subscription::create(
            SubscriptionId::generate(),
            UserId::fromString($cmd->userId),
            PlanId::fromString($cmd->planId),
            new \DateTimeImmutable(),
            new \DateTimeImmutable($cmd->endDate)
        );

        $this->repo->add($subscription);
    }
}
