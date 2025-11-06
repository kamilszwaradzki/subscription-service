<?php

declare(strict_types=1);

namespace App\Domain\Payment;

use InvalidArgumentException;

final class PaymentWebhookPayload
{
    public function __construct(
        public readonly string $eventId,
        public readonly string $subscriptionId,
        public readonly string $userId,
        public readonly string $type,
        public readonly string $signature,
    ) {
        if ($this->eventId === '' || $this->subscriptionId === '' || $this->userId === '' || $this->type === '') {
            throw new InvalidArgumentException('Webhook payload incomplete');
        }

        if (!in_array($this->type, ['payment.failed', 'payment.succeeded'], true)) {
            throw new InvalidArgumentException('Unsupported webhook type');
        }
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['event_id'] ?? '',
            $data['subscription_id'] ?? '',
            $data['user_id'] ?? '',
            $data['type'] ?? '',
            $data['signature'] ?? ''
        );
    }
}
