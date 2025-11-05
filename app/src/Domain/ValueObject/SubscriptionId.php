<?php

declare(strict_types=1);

namespace App\Domain\ValueObject;

use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

final class SubscriptionId
{
    private UuidInterface $uuid;

    private function __construct(string|UuidInterface|null $uuid = null)
    {
        $this->uuid = $uuid instanceof UuidInterface ? $uuid : ($uuid ? Uuid::fromString($uuid) : Uuid::uuid4());
    }

    public static function fromString(string $id): self
    {
        return new self(Uuid::fromString($id));
    }

    public static function generate(): self
    {
        return new self(Uuid::uuid4());
    }

    public function value(): string
    {
        return $this->uuid->toString();
    }

    public function __toString(): string
    {
        return $this->value();
    }
}
