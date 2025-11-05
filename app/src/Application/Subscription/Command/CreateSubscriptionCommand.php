<?php

namespace App\Application\Subscription\Command;

class CreateSubscriptionCommand
{
    public function __construct(
        public readonly string $userId,
        public readonly string $planId,
        public readonly string $endDate
    ) {}
}
