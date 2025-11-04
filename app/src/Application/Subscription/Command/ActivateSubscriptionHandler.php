<?php

declare(strict_types=1);

namespace App\Application\Subscription\Command;

use App\Domain\Subscription\SubscriptionRepository;

final class ActivateSubscriptionHandler
{
    public function __construct(
        private SubscriptionRepository $repository
    ) {}

    public function __invoke(ActivateSubscriptionCommand $command): void
    {
        $subscription = $this->repository->getById($command->id);
        $subscription->activate();
        $this->repository->save($subscription);
    }
}
