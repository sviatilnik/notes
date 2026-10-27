<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use App\Domain\Note\Model\NoteTitle;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261027000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Создаёт таблицу заметок';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(sprintf(
            'CREATE TABLE notes (id VARCHAR(36) NOT NULL, title VARCHAR(%d) NOT NULL, content CLOB NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, PRIMARY KEY(id))',
            NoteTitle::MAX_LENGTH,
        ));
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE notes');
    }
}
