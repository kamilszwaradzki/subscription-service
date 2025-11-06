<?php

declare(strict_types=1);

namespace App\Application\Payment;

use App\Domain\Payment\PaymentWebhookPayload;
use RuntimeException;

final class SignatureValidator
{
    public function __construct(private string $secret) {}

    public function validate(PaymentWebhookPayload $payload): void
    {
        $expected = hash_hmac('sha256', json_encode($payload->eventId), $this->secret);

        if (!hash_equals($expected, $payload->signature)) {
            throw new RuntimeException('Invalid webhook signature');
        }
    }
}
