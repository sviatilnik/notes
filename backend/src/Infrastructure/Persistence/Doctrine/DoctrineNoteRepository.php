<?php

namespace App\Infrastructure\Persistence\Doctrine;

use App\Domain\Note\Exception\NoteNotFoundException;
use App\Domain\Note\Model\Note;
use App\Domain\Note\Model\NoteId;
use App\Domain\Note\Repository\NoteRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;

final class DoctrineNoteRepository implements NoteRepositoryInterface
{
    public function __construct(
        private readonly EntityManagerInterface $em,
    ) {
    }

    public function get(NoteId $id): Note
    {
        return $this->record($id)->toNote();
    }

    public function save(Note $note): void
    {
        $record = $this->em->find(NoteRecord::class, $note->id()->value());
        if ($record instanceof NoteRecord) {
            $record->syncFrom($note);
        } else {
            $this->em->persist(NoteRecord::fromNote($note));
        }

        $this->em->flush();
    }

    public function remove(Note $note): void
    {
        $this->em->remove($this->record($note->id()));
        $this->em->flush();
    }

    private function record(NoteId $id): NoteRecord
    {
        $record = $this->em->find(NoteRecord::class, $id->value());
        if (!$record instanceof NoteRecord) {
            throw NoteNotFoundException::withId($id);
        }

        return $record;
    }
}
