<?php

namespace App\Application\Note\Command;

final readonly class UpdateNoteCommand
{
    public function __construct(
        public string $id,
        public string $title,
        public string $content,
    ) {
    }
}
