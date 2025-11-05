<?php

namespace App\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Domain\ValueObject\UserId;
use App\Domain\ValueObject\PlanId;

class CreateSubscriptionTest extends WebTestCase
{
    public function testCreateSubscriptionEndpointWorks()
    {
        $client = static::createClient();
        $client->catchExceptions(false);
        $client->request(
            'POST',
            '/subscriptions',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'user_id' => (string) UserId::generate(),
                'plan_id' => (string) PlanId::generate(),
                'end_date' => '2023-03-21'
            ])
        );

        $this->assertResponseStatusCodeSame(201);
    }
}
