<?php

namespace App\Application\Note\UseCase;

use App\Application\Note\Query\ListNotesQuery;
use App\Application\Note\ReadModel\NoteListReader;
use App\Application\Note\ReadModel\NoteListView;

final class ListNotesHandler
{
    public function __construct(
        private readonly NoteListReader $notes,
    ) {
    }

    public function handle(ListNotesQuery $query): NoteListView
    {
        return $this->notes->list($query->limit, $query->offset);
    }
}
