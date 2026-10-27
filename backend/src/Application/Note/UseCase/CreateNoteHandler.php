<?php

namespace App\Application\Note\UseCase;

use App\Application\Note\Command\CreateNoteCommand;
use App\Application\Note\ReadModel\NoteView;
use App\Domain\Note\Model\Note;
use App\Domain\Note\Model\NoteContent;
use App\Domain\Note\Model\NoteTitle;
use App\Domain\Note\Repository\NoteRepositoryInterface;

final class CreateNoteHandler
{
    public function __construct(
        private readonly NoteRepositoryInterface $notes,
    ) {
    }

    public function handle(CreateNoteCommand $command): NoteView
    {
        $note = Note::create(
            NoteTitle::fromString($command->title),
            NoteContent::fromString($command->content),
            new \DateTimeImmutable(),
        );
        $this->notes->save($note);

        return NoteView::fromNote($note);
    }
}
