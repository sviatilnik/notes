<?php

namespace App\Application\Note\ReadModel;

use App\Domain\Note\Model\Note;

final readonly class NoteView
{
    public function __construct(
        public string $id,
        public string $title,
        public string $content,
        public string $createdAt,
        public string $updatedAt,
    ) {
    }

    public static function fromNote(Note $note): self
    {
        return new self(
            $note->id()->value(),
            $note->title()->value(),
            $note->content()->value(),
            $note->createdAt()->format(\DateTimeInterface::ATOM),
            $note->updatedAt()->format(\DateTimeInterface::ATOM),
        );
    }
}
