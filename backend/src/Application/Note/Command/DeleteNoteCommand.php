<?php

namespace App\Application\Note\Command;

final readonly class DeleteNoteCommand
{
    public function __construct(
        public string $id,
    ) {
    }
}
