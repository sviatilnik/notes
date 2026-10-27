<?php

namespace App\Domain\Note\Model;

use App\Domain\Note\Exception\InvalidNoteException;

final class NoteContent
{
    public const MAX_LENGTH = 20000;

    private function __construct(
        private readonly string $value,
    ) {
    }

    public static function fromString(string $value): self
    {
        $content = trim($value);
        if ($content === '') {
            throw new InvalidNoteException('content', 'Укажите текст заметки.');
        }
        if (mb_strlen($content) > self::MAX_LENGTH) {
            throw new InvalidNoteException('content', sprintf('Текст не может быть длиннее %d символов.', self::MAX_LENGTH));
        }

        return new self($content);
    }

    public function value(): string
    {
        return $this->value;
    }
}
