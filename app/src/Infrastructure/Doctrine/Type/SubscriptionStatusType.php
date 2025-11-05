<?php

declare(strict_types=1);

namespace App\Infrastructure\Doctrine\Type;

use App\Domain\Subscription\SubscriptionStatus;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\StringType;

final class SubscriptionStatusType extends StringType
{
    public const NAME = 'subscription_status';

    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof SubscriptionStatus) {
            return $value->value;
        }

        throw new \InvalidArgumentException('Expected SubscriptionStatus.');
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): ?SubscriptionStatus
    {
        if ($value === null) {
            return null;
        }

        return SubscriptionStatus::from($value);
    }

    public function getName(): string
    {
        return self::NAME;
    }
}
