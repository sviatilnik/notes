<?php

namespace App\Application\Note\UseCase;

use App\Application\Note\Command\UpdateNoteCommand;
use App\Application\Note\ReadModel\NoteView;
use App\Domain\Note\Model\NoteContent;
use App\Domain\Note\Model\NoteId;
use App\Domain\Note\Model\NoteTitle;
use App\Domain\Note\Repository\NoteRepositoryInterface;

final class UpdateNoteHandler
{
    public function __construct(
        private readonly NoteRepositoryInterface $notes,
    ) {
    }

    public function handle(UpdateNoteCommand $command): NoteView
    {
        $note = $this->notes->get(NoteId::fromString($command->id));
        $note->rewrite(
            NoteTitle::fromString($command->title),
            NoteContent::fromString($command->content),
            new \DateTimeImmutable(),
        );
        $this->notes->save($note);

        return NoteView::fromNote($note);
    }
}
