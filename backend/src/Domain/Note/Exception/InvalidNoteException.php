<?php

namespace App\Domain\Note\Exception;

class InvalidNoteException extends \DomainException
{
    public function __construct(
        private readonly string $field,
        string $message,
    ) {
        parent::__construct($message);
    }

    public function field(): string
    {
        return $this->field;
    }
}
