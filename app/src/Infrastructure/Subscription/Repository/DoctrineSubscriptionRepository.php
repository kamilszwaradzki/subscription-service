<?php

declare(strict_types=1);

namespace App\Infrastructure\Subscription\Repository;

use App\Domain\Subscription\Subscription;
use App\Domain\Subscription\SubscriptionRepository;
use App\Domain\ValueObject\SubscriptionId;
use App\Domain\ValueObject\UserId;
use Doctrine\ORM\EntityManagerInterface;

final class DoctrineSubscriptionRepository implements SubscriptionRepository
{
    public function __construct(private EntityManagerInterface $em)
    {
    }

    public function add(Subscription $subscription): void
    {
        $this->em->persist($subscription);
        $this->em->flush();
    }

    public function update(Subscription $subscription): void
    {
        $this->em->flush();
    }

    public function get(SubscriptionId $id): Subscription
    {
        $subscription = $this->em->find(Subscription::class, $id);
        if (!$subscription) {
            throw new \RuntimeException('Subscription not found');
        }
        return $subscription;
    }

    public function findByUserId(UserId $userId): ?Subscription
    {
        return $this->em->getRepository(Subscription::class)
            ->findOneBy(['userId' => $userId]);
    }

    public function findDueForRenewal(\DateTimeImmutable $currentTime): array
    {
        return $this->em->getRepository(Subscription::class)
            ->createQueryBuilder('s')
            ->where('s.endDate <= :now')
            ->andWhere('s.status = :active')
            ->setParameter('now', $currentTime)
            ->setParameter('active', 'active')
            ->getQuery()
            ->getResult();
    }

    public function findExpiringGracePeriods(\DateTimeImmutable $currentTime): array
    {
        return $this->em->getRepository(Subscription::class)
            ->createQueryBuilder('s')
            ->where('s.graceUntil <= :now')
            ->andWhere('s.status = :grace')
            ->setParameter('now', $currentTime)
            ->setParameter('grace', 'grace_period')
            ->getQuery()
            ->getResult();
    }
}
