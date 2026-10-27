<?php

namespace App\Domain\Note\Exception;

use App\Domain\Note\Model\NoteId;

class NoteNotFoundException extends \RuntimeException
{
    public static function withId(NoteId $id): self
    {
        return new self(sprintf('Заметка %s не найдена', $id->value()));
    }
}
