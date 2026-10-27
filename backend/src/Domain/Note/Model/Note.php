<?php

namespace App\Domain\Note\Model;

final class Note
{
    private function __construct(
        private readonly NoteId $id,
        private NoteTitle $title,
        private NoteContent $content,
        private readonly \DateTimeImmutable $createdAt,
        private \DateTimeImmutable $updatedAt,
    ) {
    }

    public static function create(NoteTitle $title, NoteContent $content, \DateTimeImmutable $now): self
    {
        return new self(NoteId::generate(), $title, $content, $now, $now);
    }

    public static function restore(
        NoteId $id,
        NoteTitle $title,
        NoteContent $content,
        \DateTimeImmutable $createdAt,
        \DateTimeImmutable $updatedAt,
    ): self {
        return new self($id, $title, $content, $createdAt, $updatedAt);
    }

    public function rewrite(NoteTitle $title, NoteContent $content, \DateTimeImmutable $now): void
    {
        $this->title = $title;
        $this->content = $content;
        $this->updatedAt = $now;
    }

    public function id(): NoteId
    {
        return $this->id;
    }

    public function title(): NoteTitle
    {
        return $this->title;
    }

    public function content(): NoteContent
    {
        return $this->content;
    }

    public function createdAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function updatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }
}
