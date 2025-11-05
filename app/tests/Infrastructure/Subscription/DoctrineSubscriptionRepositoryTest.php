<?php

declare(strict_types=1);

namespace Tests\Infrastructure\Subscription;

use App\Domain\Subscription\Subscription;
use App\Domain\ValueObject\SubscriptionId;
use App\Domain\ValueObject\UserId;
use App\Domain\ValueObject\PlanId;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\Tools\SchemaTool;
use PHPUnit\Framework\TestCase;
use Doctrine\ORM\Mapping\Driver\AttributeDriver;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

class DoctrineSubscriptionRepositoryTest extends TestCase
{
    private EntityManager $em;

    protected function setUp(): void
    {
        $paths = [__DIR__ . '/../../../../src/Domain/Subscription'];

        $cache = new ArrayAdapter();

        $config = new Configuration();
        $config->setMetadataCache($cache);
        $config->setQueryCache($cache);
        $config->setProxyDir(sys_get_temp_dir());
        $config->setProxyNamespace('Proxies');
        $config->setAutoGenerateProxyClasses(true);

        // driver i metadata
        $driver = new AttributeDriver($paths);
        $config->setMetadataDriverImpl($driver);

        // połączenie SQLite in-memory
        $connection = \Doctrine\DBAL\DriverManager::getConnection([
            'driver' => 'pdo_sqlite',
            'memory' => true,
        ], $config);

        $this->em = new EntityManager($connection, $config);

        // utworzenie schematu z encji
        $schemaTool = new SchemaTool($this->em);
        $metadata = [$this->em->getClassMetadata(Subscription::class)];
        $schemaTool->createSchema($metadata);
    }

    public function test_add_and_get_subscription(): void
    {
        $subscription = Subscription::create(
            SubscriptionId::generate(),
            UserId::generate(),
            PlanId::generate(),
            new \DateTimeImmutable(),
            new \DateTimeImmutable('+1 month')
        );

        $this->em->persist($subscription);
        $this->em->flush();
        $this->em->clear();

        $fetched = $this->em->find(Subscription::class, $subscription->getId());
        $this->assertEquals($subscription->getId(), $fetched->getId());
    }

    public function test_update_subscription(): void
    {
        $subscription = Subscription::create(
            SubscriptionId::generate(),
            UserId::generate(),
            PlanId::generate(),
            new \DateTimeImmutable(),
            new \DateTimeImmutable('+1 month')
        );

        $this->em->persist($subscription);
        $this->em->flush();

        // zmiana statusu
        $subscription->activate();
        $this->em->flush();
        $this->em->clear();

        $updated = $this->em->find(Subscription::class, $subscription->getId());
        $this->assertEquals('active', $updated->getStatus()->value);
    }
}
