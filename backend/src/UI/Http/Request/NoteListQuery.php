<?php

namespace App\UI\Http\Request;

use Symfony\Component\Validator\Constraints as Assert;

class NoteListQuery
{
    #[Assert\Range(
        min: 1,
        max: 100,
        notInRangeMessage: 'Лимит должен быть от {{ min }} до {{ max }}.'
    )]
    public ?int $limit = null;

    #[Assert\GreaterThanOrEqual(
        value: 0,
        message: 'Смещение не может быть отрицательным.'
    )]
    public ?int $offset = null;
}
