<?php

namespace App\Infrastructure\Subscription\Repository;

use App\Domain\Subscription\Subscription;
use App\Domain\ValueObject\SubscriptionId;
use App\Domain\Subscription\SubscriptionRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;

final class DoctrineSubscriptionRepository implements SubscriptionRepositoryInterface
{
    public function __construct(private EntityManagerInterface $em) {}

    public function save(Subscription $subscription): void
    {
        $this->em->persist($subscription);
        $this->em->flush();
    }

    public function get(SubscriptionId $id): Subscription
    {
        $subscription = $this->em->find(Subscription::class, $id);
        if (!$subscription) {
            throw new \RuntimeException('Subscription not found: ' . $id->toString());
        }

        return $subscription;
    }
}
