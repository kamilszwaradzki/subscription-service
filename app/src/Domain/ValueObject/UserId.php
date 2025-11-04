<?php

declare(strict_types=1);

namespace App\Domain\ValueObject;

use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

final class UserId
{
    private function __construct(
        private readonly UuidInterface $value
    ) {}

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
        return $this->value->toString();
    }

    public function __toString(): string
    {
        return $this->value();
    }
}
