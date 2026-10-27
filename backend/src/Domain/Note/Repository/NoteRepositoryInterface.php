<?php

namespace App\Domain\Note\Repository;

use App\Domain\Note\Model\Note;
use App\Domain\Note\Model\NoteId;

interface NoteRepositoryInterface
{
    public function get(NoteId $id): Note;

    public function save(Note $note): void;

    public function remove(Note $note): void;
}
