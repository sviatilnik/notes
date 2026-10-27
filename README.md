# Мини-сервис заметок

Веб-сервис для просмотра, создания, редактирования и удаления заметок.

- Backend: **Symfony 7** REST API
- Frontend: **Vue 3**
- База: **SQLite**
- Документация API: **Swagger / OpenAPI** (`/api/doc`)
- Тесты: **PHPUnit**
- Запуск: **Docker Compose**

## Быстрый старт

Нужен установленный Docker Desktop.

Есть два compose-файла: продакшен и разработка. Оба поднимают связку frontend + backend.

### Продакшен

Собранный Vue отдаёт Nginx, API идёт в PHP-FPM.

```bash
docker compose -f docker-compose.yml up -d --build
```

### Разработка

```bash
docker compose -f docker-compose.dev.yml up -d --build
```

## Тесты

В контейнере разработки:

```bash
docker compose -f docker-compose.dev.yml exec php vendor/bin/phpunit
```
