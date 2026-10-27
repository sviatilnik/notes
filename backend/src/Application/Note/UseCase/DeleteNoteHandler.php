<?php

namespace App\Application\Note\UseCase;

use App\Application\Note\Command\DeleteNoteCommand;
use App\Domain\Note\Model\NoteId;
use App\Domain\Note\Repository\NoteRepositoryInterface;

final class DeleteNoteHandler
{
    public function __construct(
        private readonly NoteRepositoryInterface $notes,
    ) {
    }

    public function handle(DeleteNoteCommand $command): void
    {
        $note = $this->notes->get(NoteId::fromString($command->id));
        $this->notes->remove($note);
    }
}
