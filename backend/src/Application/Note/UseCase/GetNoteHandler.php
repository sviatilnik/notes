<?php

namespace App\Application\Note\UseCase;

use App\Application\Note\Query\GetNoteQuery;
use App\Application\Note\ReadModel\NoteView;
use App\Domain\Note\Model\NoteId;
use App\Domain\Note\Repository\NoteRepositoryInterface;

final class GetNoteHandler
{
    public function __construct(
        private readonly NoteRepositoryInterface $notes,
    ) {
    }

    public function handle(GetNoteQuery $query): NoteView
    {
        return NoteView::fromNote($this->notes->get(NoteId::fromString($query->id)));
    }
}
