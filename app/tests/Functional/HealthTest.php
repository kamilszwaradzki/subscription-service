<?php

namespace App\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class HealthTest extends WebTestCase
{
    public function testHealthEndpointWorks()
    {
        $client = static::createClient();
        $client->catchExceptions(false);
        $client->request('GET', '/health');

        $this->assertResponseIsSuccessful();
        $this->assertStringContainsString('ok', $client->getResponse()->getContent());
    }
}
