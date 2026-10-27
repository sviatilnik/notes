<?php

namespace App\Application\Note\Query;

final readonly class GetNoteQuery
{
    public function __construct(
        public string $id,
    ) {
    }
}
