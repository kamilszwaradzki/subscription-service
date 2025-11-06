<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Application\Subscription\Command\FailSubscriptionPaymentCommand;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

class WebhookPaymentTest extends WebTestCase
{
    public function test_payment_failed_event_dispatches_command(): void
    {
        $client = static::createClient();

        $subscriptionId = Uuid::uuid4()->toString();
        $eventId = 'evt_123';
        $secret = $_ENV['PAYMENT_SECRET'] ?? 'test_secret';

        $expectedSignature = hash_hmac('sha256', json_encode($eventId), $secret);

        $payload = [
            'event_id' => $eventId,
            'user_id' => 'user_123',
            'subscription_id' => $subscriptionId,
            'type' => 'payment.failed',
            'signature' => $expectedSignature
        ];

        $bus = new class implements MessageBusInterface {
            public array $dispatched = [];

            public function dispatch(object $message, array $stamps = []): Envelope
            {
                $this->dispatched[] = $message;
                return new Envelope($message);
            }
        };

        self::getContainer()->set(MessageBusInterface::class, $bus);

        // request
        $client->request(
            'POST',
            '/api/webhook/payment',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($payload)
        );

        $this->assertResponseIsSuccessful();
        $this->assertCount(1, $bus->dispatched);
        $this->assertInstanceOf(FailSubscriptionPaymentCommand::class, $bus->dispatched[0]);
    }

    public function test_invalid_signature_returns_400(): void
    {
        $client = static::createClient();

        $subscriptionId = Uuid::uuid4()->toString();

        // specjalnie zły podpis
        $payload = [
            'event_id' => 'evt_456',
            'user_id' => 'user_123',
            'subscription_id' => $subscriptionId,
            'type' => 'payment.failed',
            'signature' => 'invalid_signature'
        ];

        $client->request(
            'POST',
            '/api/webhook/payment',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($payload)
        );

        $this->assertResponseStatusCodeSame(400);
    }

    public function test_missing_signature_returns_400(): void
    {
        $client = static::createClient();

        $payload = [
            'event_id' => 'evt_789',
            'user_id' => 'user_123',
            'subscription_id' => Uuid::uuid4()->toString(),
            'type' => 'payment.failed',
            // brak 'signature'
        ];

        $client->request(
            'POST',
            '/api/webhook/payment',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($payload)
        );

        $this->assertResponseStatusCodeSame(400);
    }
}
