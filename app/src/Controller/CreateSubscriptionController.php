<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use App\Application\Subscription\Command\CreateSubscriptionCommand;
use App\Application\Subscription\Command\CreateSubscriptionHandler;

class CreateSubscriptionController extends AbstractController
{
    #[Route('/subscriptions', methods: ['POST'])]
    public function create(Request $request, CreateSubscriptionHandler $handler): JsonResponse
    {
        $payload = $request->toArray(); // symfony helper

        $command = new CreateSubscriptionCommand(
            $payload['user_id'],
            $payload['plan_id'],
            $payload['end_date']
        );

        $handler($command);

        return $this->json(null, 201);
    }
}