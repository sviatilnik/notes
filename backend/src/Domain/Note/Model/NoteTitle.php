<?php

namespace App\Domain\Note\Model;

use App\Domain\Note\Exception\InvalidNoteException;

final class NoteTitle
{
    public const MAX_LENGTH = 255;

    private function __construct(
        private readonly string $value,
    ) {
    }

    public static function fromString(string $value): self
    {
        $title = trim($value);
        if ($title === '') {
            throw new InvalidNoteException('title', 'Укажите заголовок.');
        }
        if (mb_strlen($title) > self::MAX_LENGTH) {
            throw new InvalidNoteException('title', sprintf('Заголовок не может быть длиннее %d символов.', self::MAX_LENGTH));
        }

        return new self($title);
    }

    public function value(): string
    {
        return $this->value;
    }
}
