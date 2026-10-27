<?php

namespace App\UI\Http\EventSubscriber;

use App\Domain\Note\Exception\InvalidNoteException;
use App\Domain\Note\Exception\NoteNotFoundException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Validator\Exception\ValidationFailedException;

class ApiExceptionSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => 'onException',
        ];
    }

    public function onException(ExceptionEvent $event): void
    {
        if (!$this->isApiRequest($event)) {
            return;
        }

        $exception = $event->getThrowable();
        $validation = $this->extractValidation($exception);

        if ($validation instanceof ValidationFailedException) {
            $status = $exception instanceof HttpExceptionInterface
                ? $exception->getStatusCode()
                : Response::HTTP_UNPROCESSABLE_ENTITY;

            $event->setResponse(new JsonResponse([
                'error' => 'Ошибка валидации',
                'violations' => $this->formatViolations($validation),
            ], $status));

            return;
        }

        if ($exception instanceof InvalidNoteException) {
            $event->setResponse(new JsonResponse([
                'error' => 'Ошибка валидации',
                'violations' => [
                    ['field' => $exception->field(), 'message' => $exception->getMessage()],
                ],
            ], Response::HTTP_UNPROCESSABLE_ENTITY));

            return;
        }

        if ($exception instanceof BadRequestHttpException) {
            $event->setResponse(new JsonResponse([
                'error' => 'Ошибка валидации',
                'violations' => [
                    ['field' => 'body', 'message' => 'Тело запроса должно быть корректным JSON.'],
                ],
            ], Response::HTTP_UNPROCESSABLE_ENTITY));

            return;
        }

        if ($exception instanceof NoteNotFoundException) {
            $event->setResponse(new JsonResponse([
                'error' => 'Заметка не найдена',
            ], Response::HTTP_NOT_FOUND));

            return;
        }

        $status = $exception instanceof HttpExceptionInterface
            ? $exception->getStatusCode()
            : Response::HTTP_INTERNAL_SERVER_ERROR;

        $event->setResponse(new JsonResponse([
            'error' => $this->errorMessage($status),
        ], $status));
    }

    private function isApiRequest(ExceptionEvent $event): bool
    {
        $path = $event->getRequest()->getPathInfo();

        return str_starts_with($path, '/api') && !str_starts_with($path, '/api/doc');
    }

    private function errorMessage(int $status): string
    {
        return match ($status) {
            Response::HTTP_BAD_REQUEST => 'Некорректный запрос',
            Response::HTTP_NOT_FOUND => 'Не найдено',
            Response::HTTP_METHOD_NOT_ALLOWED => 'Метод не поддерживается',
            Response::HTTP_NOT_ACCEPTABLE, Response::HTTP_UNSUPPORTED_MEDIA_TYPE => 'Неподдерживаемый формат',
            default => $status >= 500 ? 'Внутренняя ошибка' : 'Ошибка запроса',
        };
    }

    private function extractValidation(\Throwable $exception): ?ValidationFailedException
    {
        if ($exception instanceof ValidationFailedException) {
            return $exception;
        }

        $previous = $exception->getPrevious();

        return $previous instanceof ValidationFailedException ? $previous : null;
    }

    /**
     * @return list<array{field: string, message: string}>
     */
    private function formatViolations(ValidationFailedException $exception): array
    {
        $items = [];
        foreach ($exception->getViolations() as $violation) {
            $items[] = [
                'field' => $violation->getPropertyPath(),
                'message' => (string) $violation->getMessage(),
            ];
        }

        return $items;
    }
}
