<?php

namespace App\Tests\Api;

use App\Domain\Note\Model\Note;
use App\Domain\Note\Model\NoteContent;
use App\Domain\Note\Model\NoteTitle;
use App\Domain\Note\Repository\NoteRepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

class NoteApiTest extends WebTestCase
{
    private KernelBrowser $client;

    protected function setUp(): void
    {
        self::ensureKernelShutdown();
        $this->client = static::createClient();
        $this->resetSchema();
    }

    private function resetSchema(): void
    {
        $connection = static::getContainer()->get('doctrine')->getConnection();
        foreach ($connection->createSchemaManager()->listTableNames() as $table) {
            $connection->executeStatement(sprintf(
                'DROP TABLE IF EXISTS %s',
                $connection->quoteSingleIdentifier($table),
            ));
        }

        $application = new Application(self::$kernel);
        $application->setAutoExit(false);
        $status = $application->run(new ArrayInput([
            'command' => 'doctrine:migrations:migrate',
            '--no-interaction' => true,
            '--quiet' => true,
        ]), new NullOutput());

        self::assertSame(0, $status);
    }

    public function testListReturnsEmptyArray(): void
    {
        $this->client->request('GET', '/api/notes');

        self::assertResponseIsSuccessful();
        $data = $this->decode();
        self::assertSame([], $data['items']);
        self::assertSame(0, $data['total']);
    }

    public function testCreateNote(): void
    {
        $this->client->request(
            'POST',
            '/api/notes',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode(['title' => 'Первая', 'content' => 'Текст заметки'], JSON_THROW_ON_ERROR)
        );

        self::assertResponseStatusCodeSame(201);
        $data = $this->decode();
        self::assertSame('Первая', $data['title']);
        self::assertSame('Текст заметки', $data['content']);
        self::assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/',
            $data['id'],
        );
        self::assertNotEmpty($data['createdAt']);
        self::assertNotEmpty($data['updatedAt']);
    }

    public function testCreateValidatesMissingFields(): void
    {
        $this->client->request(
            'POST',
            '/api/notes',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode(['title' => ''], JSON_THROW_ON_ERROR)
        );

        self::assertResponseStatusCodeSame(422);
        $data = $this->decode();
        self::assertSame('Ошибка валидации', $data['error']);
        self::assertNotEmpty($data['violations']);
    }

    public function testCreateValidatesInvalidJson(): void
    {
        $this->client->request(
            'POST',
            '/api/notes',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: '{bad json'
        );

        self::assertResponseStatusCodeSame(422);
        $data = $this->decode();
        self::assertSame('body', $data['violations'][0]['field']);
    }

    public function testShowNote(): void
    {
        $id = $this->createNote('Заголовок', 'Содержимое');

        $this->client->request('GET', '/api/notes/'.$id);

        self::assertResponseIsSuccessful();
        $data = $this->decode();
        self::assertSame($id, $data['id']);
        self::assertSame('Заголовок', $data['title']);
    }

    public function testShowValidatesNonNumericId(): void
    {
        $this->client->request('GET', '/api/notes/abc');

        self::assertResponseStatusCodeSame(404);
        $data = $this->decode();
        self::assertSame('Не найдено', $data['error']);
    }

    public function testShowReturns404(): void
    {
        $this->client->request('GET', '/api/notes/550e8400-e29b-41d4-a716-446655440000');

        self::assertResponseStatusCodeSame(404);
        $data = $this->decode();
        self::assertSame('Заметка не найдена', $data['error']);
    }

    public function testUpdateNote(): void
    {
        $id = $this->createNote('Старый', 'Текст');

        $this->client->request(
            'PUT',
            '/api/notes/'.$id,
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode(['title' => 'Новый', 'content' => 'Обновлённый текст'], JSON_THROW_ON_ERROR)
        );

        self::assertResponseIsSuccessful();
        $data = $this->decode();
        self::assertSame('Новый', $data['title']);
        self::assertSame('Обновлённый текст', $data['content']);
    }

    public function testUpdateValidatesTooLongTitle(): void
    {
        $id = $this->createNote('Ок', 'Текст');

        $this->client->request(
            'PUT',
            '/api/notes/'.$id,
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode(['title' => str_repeat('a', 256), 'content' => 'Текст'], JSON_THROW_ON_ERROR)
        );

        self::assertResponseStatusCodeSame(422);
    }

    public function testDeleteNote(): void
    {
        $id = $this->createNote('Удалить', 'Текст');

        $this->client->request('DELETE', '/api/notes/'.$id);

        self::assertResponseStatusCodeSame(204);

        $this->client->request('GET', '/api/notes/'.$id);
        self::assertResponseStatusCodeSame(404);
    }

    public function testDeleteValidatesId(): void
    {
        $this->client->request('DELETE', '/api/notes/0');

        self::assertResponseStatusCodeSame(404);
    }

    public function testListReturnsCreatedNotes(): void
    {
        $this->createNote('A', '1');
        $this->createNote('B', '2');

        $this->client->request('GET', '/api/notes');

        self::assertResponseIsSuccessful();
        $data = $this->decode();
        self::assertCount(2, $data['items']);
        self::assertSame(2, $data['total']);
    }

    public function testListValidatesLimit(): void
    {
        $this->client->request('GET', '/api/notes?limit=0');

        self::assertResponseStatusCodeSame(422);
    }

    public function testListValidatesOffset(): void
    {
        $this->client->request('GET', '/api/notes?offset=-1');

        self::assertResponseStatusCodeSame(422);
    }

    public function testListAppliesLimitAndOffset(): void
    {
        $this->createNote('A', '1');
        $this->createNote('B', '2');
        $this->createNote('C', '3');

        $this->client->request('GET', '/api/notes?limit=1&offset=1');

        self::assertResponseIsSuccessful();
        $data = $this->decode();
        self::assertCount(1, $data['items']);
        self::assertSame(3, $data['total']);
    }

    private function createNote(string $title, string $content): string
    {
        $notes = static::getContainer()->get(NoteRepositoryInterface::class);
        $note = Note::create(
            NoteTitle::fromString($title),
            NoteContent::fromString($content),
            new \DateTimeImmutable(),
        );
        $notes->save($note);

        return $note->id()->value();
    }

    /**
     * @return array<mixed>
     */
    private function decode(): array
    {
        $content = $this->client->getResponse()->getContent();
        self::assertIsString($content);

        return json_decode($content, true, 512, JSON_THROW_ON_ERROR);
    }
}
