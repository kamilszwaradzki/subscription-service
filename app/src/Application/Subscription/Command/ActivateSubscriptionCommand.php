<?php

declare(strict_types=1);

namespace App\Application\Subscription\Command;

use App\Domain\ValueObject\SubscriptionId;

final class ActivateSubscriptionCommand
{
    public function __construct(
        public readonly SubscriptionId $id
    ) {}
}
