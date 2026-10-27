<?php

namespace App\UI\Http\Request;

class NoteInput
{
    public string $title = '';

    public string $content = '';

    public function title(): string
    {
        return trim($this->title);
    }

    public function content(): string
    {
        return trim($this->content);
    }
}
