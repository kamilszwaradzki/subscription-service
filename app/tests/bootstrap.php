<?php

use Symfony\Component\Dotenv\Dotenv;
use App\Kernel;
use Doctrine\DBAL\Types\Type;
use App\Infrastructure\Doctrine\Type\SubscriptionIdType;
use App\Infrastructure\Doctrine\Type\UserIdType;
use App\Infrastructure\Doctrine\Type\PlanIdType;
use App\Infrastructure\Doctrine\Type\SubscriptionStatusType;

require dirname(__DIR__) . '/vendor/autoload.php';

(new Dotenv())->bootEnv(dirname(__DIR__).'/.env.test');

// Load Symfony kernel so Doctrine config loads properly
$kernel = new Kernel($_SERVER['APP_ENV'], (bool) $_SERVER['APP_DEBUG']);
$kernel->boot();

// Register custom types manually for safety
$types = [
    'subscription_id' => SubscriptionIdType::class,
    'user_id' => UserIdType::class,
    'plan_id' => PlanIdType::class,
    'subscription_status' => SubscriptionStatusType::class,
];

foreach ($types as $name => $class) {
    if (!Type::hasType($name)) {
        Type::addType($name, $class);
    }
}
