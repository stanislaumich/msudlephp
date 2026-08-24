# AGENTS.md

## Команды для проверки кода

### Проверка синтаксиса PHP
```bash
D:\OpenServer\modules\php\PHP_8.1\php.exe -l path/to/file.php
```

### Запуск unit-тестов
```bash
D:\OpenServer\modules\php\PHP_8.1\php.exe tmp_unit_tests.php
```

### Локальный сервер (разработка)
```bash
D:\OpenServer\modules\php\PHP_8.1\php.exe -S 127.0.0.1:9876 -t public
```

## Структура проекта

- `public/` — точка входа (index.php, .htaccess)
- `app/core/` — ядро: Database, Router, Auth, Role, Csrf, View, Flash
- `app/models/` — модели (index.php содержит все модели)
- `app/controllers/` — контроллеры
- `app/views/` — шаблоны (включая layouts/)
- `app/middleware/` — Middleware (auth, teacher, student, settingsAccess)
- `app/routes/web.php` — маршруты
- `storage/` — загрузки и логи
- `.env` — конфигурация БД и окружения

## Конвенции

- Все POST-формы защищены CSRF через `Core\Csrf` (авто-инъекция через JS)
- Все шаблоны используют `htmlspecialchars` для вывода
- Пароли хэшируются через `password_hash(PASSWORD_DEFAULT)`
- Проверка прав доступа через `\Middleware\Middleware::settingsAccess()` / `teacher()` / `student()`
