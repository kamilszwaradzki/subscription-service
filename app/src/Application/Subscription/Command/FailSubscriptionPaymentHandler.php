<?php

namespace App\Application\Subscription\Command;

use App\Domain\Subscription\SubscriptionRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class FailSubscriptionPaymentHandler
{
    public function __construct(private SubscriptionRepository $repo) {}

    public function __invoke(FailSubscriptionPaymentCommand $cmd): void
    {
        $subscription = $this->repo->get($cmd->id);
        $subscription->failPayment();
        $this->repo->update($subscription);
    }
}
