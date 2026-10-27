<?php

namespace App\Application\Note\Command;

final readonly class CreateNoteCommand
{
    public function __construct(
        public string $title,
        public string $content,
    ) {
    }
}
