<?php

namespace App\Infrastructure\Persistence\Doctrine;

use App\Domain\Note\Model\Note;
use App\Domain\Note\Model\NoteContent;
use App\Domain\Note\Model\NoteId;
use App\Domain\Note\Model\NoteTitle;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'notes')]
class NoteRecord
{
    #[ORM\Id]
    #[ORM\Column(length: 36)]
    private string $id;

    #[ORM\Column(length: NoteTitle::MAX_LENGTH)]
    private string $title;

    #[ORM\Column(type: Types::TEXT)]
    private string $content;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column]
    private \DateTimeImmutable $updatedAt;

    public static function fromNote(Note $note): self
    {
        $record = new self();
        $record->syncFrom($note);

        return $record;
    }

    public function syncFrom(Note $note): void
    {
        $this->id = $note->id()->value();
        $this->title = $note->title()->value();
        $this->content = $note->content()->value();
        $this->createdAt = $note->createdAt();
        $this->updatedAt = $note->updatedAt();
    }

    public function toNote(): Note
    {
        return Note::restore(
            NoteId::fromString($this->id),
            NoteTitle::fromString($this->title),
            NoteContent::fromString($this->content),
            $this->createdAt,
            $this->updatedAt,
        );
    }
}
