<?php

namespace App\Infrastructure\Persistence\Doctrine;

use App\Application\Note\ReadModel\NoteListReader;
use App\Application\Note\ReadModel\NoteListView;
use App\Application\Note\ReadModel\NoteView;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Types\Types;

final class DoctrineNoteListReader implements NoteListReader
{
    public function __construct(
        private readonly Connection $connection,
    ) {
    }

    public function list(?int $limit, int $offset): NoteListView
    {
        $page = 'SELECT id, title, content, created_at, updated_at FROM notes ORDER BY updated_at DESC, id DESC';
        $params = [];
        $types = [];

        if ($limit !== null) {
            $page .= ' LIMIT :limit OFFSET :offset';
            $params = ['limit' => $limit, 'offset' => $offset];
            $types = ['limit' => ParameterType::INTEGER, 'offset' => ParameterType::INTEGER];
        } elseif ($offset > 0) {
            $page .= ' LIMIT -1 OFFSET :offset';
            $params = ['offset' => $offset];
            $types = ['offset' => ParameterType::INTEGER];
        }

        $sql = <<<SQL
            SELECT page.id AS id,
                   page.title AS title,
                   page.content AS content,
                   page.created_at AS created_at,
                   page.updated_at AS updated_at,
                   totals.total AS total
            FROM (SELECT COUNT(*) AS total FROM notes) totals
            LEFT JOIN ($page) page ON 1 = 1
            SQL;

        $rows = $this->connection->fetchAllAssociative($sql, $params, $types);
        $items = [];
        $total = 0;

        foreach ($rows as $row) {
            $total = (int) $row['total'];
            if ($row['id'] === null) {
                continue;
            }

            $items[] = new NoteView(
                (string) $row['id'],
                (string) $row['title'],
                (string) $row['content'],
                $this->formatDate($row['created_at']),
                $this->formatDate($row['updated_at']),
            );
        }

        return new NoteListView($items, $total);
    }

    private function formatDate(mixed $value): string
    {
        $date = $this->connection->convertToPHPValue($value, Types::DATETIME_IMMUTABLE);
        if (!$date instanceof \DateTimeImmutable) {
            throw new \UnexpectedValueException('Не удалось прочитать дату заметки.');
        }

        return $date->format(\DateTimeInterface::ATOM);
    }
}
