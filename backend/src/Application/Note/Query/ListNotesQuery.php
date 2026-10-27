<?php

namespace App\Application\Note\Query;

final readonly class ListNotesQuery
{
    public function __construct(
        public ?int $limit = null,
        public int $offset = 0,
    ) {
    }
}
