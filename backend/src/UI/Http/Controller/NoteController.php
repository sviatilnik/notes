<?php

namespace App\UI\Http\Controller;

use App\Application\Note\Command\CreateNoteCommand;
use App\Application\Note\Command\DeleteNoteCommand;
use App\Application\Note\Command\UpdateNoteCommand;
use App\Application\Note\Query\GetNoteQuery;
use App\Application\Note\Query\ListNotesQuery;
use App\Application\Note\UseCase\CreateNoteHandler;
use App\Application\Note\UseCase\DeleteNoteHandler;
use App\Application\Note\UseCase\GetNoteHandler;
use App\Application\Note\UseCase\ListNotesHandler;
use App\Application\Note\UseCase\UpdateNoteHandler;
use App\UI\Http\Request\NoteInput;
use App\UI\Http\Request\NoteListQuery as NoteListHttpQuery;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;

#[Route('/api/notes')]
#[OA\Tag(name: 'Notes')]
class NoteController extends AbstractController
{
    public function __construct(
        private readonly ListNotesHandler $listNotes,
        private readonly GetNoteHandler $getNote,
        private readonly CreateNoteHandler $createNote,
        private readonly UpdateNoteHandler $updateNote,
        private readonly DeleteNoteHandler $deleteNote,
    ) {
    }

    #[Route('', name: 'notes_list', methods: ['GET'])]
    #[OA\Get(
        path: '/api/notes',
        summary: 'Список всех заметок',
        parameters: [
            new OA\Parameter(
                name: 'limit',
                in: 'query',
                required: false,
                description: 'Лимит (1-100)',
                schema: new OA\Schema(type: 'integer', minimum: 1, maximum: 100)
            ),
            new OA\Parameter(
                name: 'offset',
                in: 'query',
                required: false,
                description: 'Смещение (от 0)',
                schema: new OA\Schema(type: 'integer', minimum: 0)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Список заметок',
                content: new OA\JsonContent(ref: '#/components/schemas/NoteList')
            ),
            new OA\Response(response: 422, description: 'Ошибка валидации', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError')),
        ]
    )]
    public function list(
        #[MapQueryString(validationFailedStatusCode: Response::HTTP_UNPROCESSABLE_ENTITY)]
        NoteListHttpQuery $query,
    ): JsonResponse {
        $list = $this->listNotes->handle(new ListNotesQuery(
            $query->limit,
            $query->offset ?? 0,
        ));

        return $this->json($list);
    }

    #[Route('/{id}', name: 'notes_show', methods: ['GET'], requirements: ['id' => Requirement::UUID])]
    #[OA\Get(
        path: '/api/notes/{id}',
        summary: 'Просмотр заметки',
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid'))],
        responses: [
            new OA\Response(response: 200, description: 'Заметка', content: new OA\JsonContent(ref: '#/components/schemas/Note')),
            new OA\Response(response: 404, description: 'Не найдена', content: new OA\JsonContent(ref: '#/components/schemas/NotFoundError')),
        ]
    )]
    public function show(string $id): JsonResponse
    {
        return $this->json($this->getNote->handle(new GetNoteQuery($id)));
    }

    #[Route('', name: 'notes_create', methods: ['POST'])]
    #[OA\Post(
        path: '/api/notes',
        summary: 'Создание заметки',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/NoteInput')
        ),
        responses: [
            new OA\Response(response: 201, description: 'Создана', content: new OA\JsonContent(ref: '#/components/schemas/Note')),
            new OA\Response(response: 422, description: 'Ошибка валидации', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError')),
        ]
    )]
    public function create(
        #[MapRequestPayload(acceptFormat: 'json')]
        NoteInput $input,
    ): JsonResponse {
        $view = $this->createNote->handle(new CreateNoteCommand($input->title(), $input->content()));

        return $this->json($view, Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'notes_update', methods: ['PUT'], requirements: ['id' => Requirement::UUID])]
    #[OA\Put(
        path: '/api/notes/{id}',
        summary: 'Редактирование заметки',
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid'))],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/NoteInput')
        ),
        responses: [
            new OA\Response(response: 200, description: 'Обновлена', content: new OA\JsonContent(ref: '#/components/schemas/Note')),
            new OA\Response(response: 404, description: 'Не найдена', content: new OA\JsonContent(ref: '#/components/schemas/NotFoundError')),
            new OA\Response(response: 422, description: 'Ошибка валидации', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError')),
        ]
    )]
    public function update(
        string $id,
        #[MapRequestPayload(acceptFormat: 'json')]
        NoteInput $input,
    ): JsonResponse {
        $view = $this->updateNote->handle(new UpdateNoteCommand($id, $input->title(), $input->content()));

        return $this->json($view);
    }

    #[Route('/{id}', name: 'notes_delete', methods: ['DELETE'], requirements: ['id' => Requirement::UUID])]
    #[OA\Delete(
        path: '/api/notes/{id}',
        summary: 'Удаление заметки',
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid'))],
        responses: [
            new OA\Response(response: 204, description: 'Удалена'),
            new OA\Response(response: 404, description: 'Не найдена', content: new OA\JsonContent(ref: '#/components/schemas/NotFoundError')),
        ]
    )]
    public function delete(string $id): JsonResponse
    {
        $this->deleteNote->handle(new DeleteNoteCommand($id));

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }
}
