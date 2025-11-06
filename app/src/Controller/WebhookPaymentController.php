<?php

namespace App\Controller;

use App\Application\Payment\SignatureValidator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use App\Application\Subscription\Command\FailSubscriptionPaymentCommand;
use App\Application\Subscription\Command\SucceedSubscriptionPaymentCommand;
use App\Domain\Payment\PaymentWebhookPayload;
use App\Domain\ValueObject\SubscriptionId;
use Symfony\Component\Messenger\MessageBusInterface;

class WebhookPaymentController extends AbstractController
{
    #[Route('/api/webhook/payment', methods: ['POST'])]
    public function validate(Request $request, MessageBusInterface $messageBus): JsonResponse
    {
        try {
            $payload = PaymentWebhookPayload::fromArray($request->toArray());

            $signatureValidator = new SignatureValidator($_ENV['PAYMENT_SECRET']);
            $signatureValidator->validate($payload);

            switch ($payload->type) {
                case 'payment.failed':
                    $command = new FailSubscriptionPaymentCommand(
                        SubscriptionId::fromString($payload->subscriptionId)
                    );
                    break;

                case 'payment.succeeded':
                    $command = new SucceedSubscriptionPaymentCommand(
                        SubscriptionId::fromString($payload->subscriptionId)
                    );
                    break;

                default:
                    return $this->json(['error' => 'Unsupported event type'], 400);
            }

            $messageBus->dispatch($command);

            return $this->json(['status' => 'ok'], 200);
        } catch (\Throwable $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }
}