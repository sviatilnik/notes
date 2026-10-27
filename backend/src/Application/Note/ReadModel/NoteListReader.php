<?php

namespace App\Application\Note\ReadModel;

interface NoteListReader
{
    public function list(?int $limit, int $offset): NoteListView;
}
