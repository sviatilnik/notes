<?php

namespace App\Tests\Domain;

use App\Domain\Note\Exception\InvalidNoteException;
use App\Domain\Note\Model\Note;
use App\Domain\Note\Model\NoteContent;
use App\Domain\Note\Model\NoteTitle;
use PHPUnit\Framework\TestCase;

class NoteTest extends TestCase
{
    public function testCreateStoresTitleAndContent(): void
    {
        $now = new \DateTimeImmutable('2026-10-27T12:00:00+00:00');
        $note = Note::create(
            NoteTitle::fromString('Список'),
            NoteContent::fromString('Молоко'),
            $now,
        );

        self::assertSame('Список', $note->title()->value());
        self::assertSame('Молоко', $note->content()->value());
        self::assertEquals($now, $note->createdAt());
        self::assertEquals($now, $note->updatedAt());
    }

    public function testRewriteUpdatesContentAndTimestamp(): void
    {
        $createdAt = new \DateTimeImmutable('2026-10-27T12:00:00+00:00');
        $note = Note::create(
            NoteTitle::fromString('Старый'),
            NoteContent::fromString('Текст'),
            $createdAt,
        );
        $updatedAt = new \DateTimeImmutable('2026-10-27T12:05:00+00:00');

        $note->rewrite(
            NoteTitle::fromString('Новый'),
            NoteContent::fromString('Обновлённый'),
            $updatedAt,
        );

        self::assertSame('Новый', $note->title()->value());
        self::assertSame('Обновлённый', $note->content()->value());
        self::assertEquals($createdAt, $note->createdAt());
        self::assertEquals($updatedAt, $note->updatedAt());
    }

    public function testTitleCannotBeBlank(): void
    {
        $this->expectException(InvalidNoteException::class);
        NoteTitle::fromString('   ');
    }
}
