<?php

namespace App\Application\Subscription\Command;

use App\Domain\Subscription\SubscriptionRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class SucceedSubscriptionPaymentHandler
{
    public function __construct(private SubscriptionRepository $repo) {}

    public function __invoke(SucceedSubscriptionPaymentCommand $cmd): void
    {
        $subscription = $this->repo->get($cmd->id);
        $subscription->markPaymentSucceeded();
        $this->repo->update($subscription);
    }
}
