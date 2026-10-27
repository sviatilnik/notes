<?php

namespace App\Domain\Note\Model;

use App\Domain\Note\Exception\InvalidNoteException;
use Symfony\Component\Uid\Uuid;

final class NoteId
{
    private function __construct(
        private readonly string $value,
    ) {
    }

    public static function generate(): self
    {
        return new self(Uuid::v4()->toRfc4122());
    }

    public static function fromString(string $value): self
    {
        try {
            $uuid = Uuid::fromString(trim($value));
        } catch (\InvalidArgumentException) {
            throw new InvalidNoteException('id', 'Идентификатор должен быть UUID.');
        }

        return new self($uuid->toRfc4122());
    }

    public function value(): string
    {
        return $this->value;
    }
}
