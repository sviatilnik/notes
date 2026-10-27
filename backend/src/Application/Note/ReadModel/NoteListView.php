<?php

namespace App\Application\Note\ReadModel;

final readonly class NoteListView
{
    /**
     * @param list<NoteView> $items
     */
    public function __construct(
        public array $items,
        public int $total,
    ) {
    }
}
