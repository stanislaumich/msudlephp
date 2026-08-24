# Архитектура MSUDLE PHP

## Обзор

MSUDLE PHP — платформа дистанционного обучения, перенесённая с Django (Python) на PHP 7.4.
Приложение использует чистый PHP без фреймворков и ORM — только PDO для работы с MySQL.

## Архитектурный подход

```
HTTP Request
    ↓
public/index.php (Front Controller)
    ↓
app/routes/web.php (Router)
    ↓
Controllers\* (Business Logic)
    ↓
Models\* + Core\Database (Data Access)
    ↓
app/views/* (Templates)
    ↓
Response (HTML)
```

## Ключевые компоненты

### public/index.php
- Единая точка входа (Front Controller)
- Загружает конфиг из `.env`
- Запускает session с httponly/cookie_secure
- Регистрирует autoloaders для Controllers\, Models\, Core\
- Проверяет CSRF токен для POST/PUT/DELETE/PATCH запросов
- Подключает маршруты

### Core\Database
Статический класс-обёртка над PDO:
- `getConnection()` — singleton PDO с ошибками в режиме EXCEPTION
- `query()` / `fetch()` / `fetchAll()` / `findOne()` — выполнение запросов
- `insert()` / `update()` / `delete()` / `count()` — DML-операции с backticks
- Автоматическая quote-борка для имен таблиц и колонок

### Core\Router
- Регистрирует GET/POST/PUT/DELETE/Any маршруты с паттернами `{param}`
- `dispatch()` находит первый совпадающий роут и вызывает контроллер
- `redirect()` и `back()` для редиректов

### Core\Auth
- Статический доступ к пользователю через `$_SESSION['user']`
- `guard()` возвращает 'teacher' или 'student' на основе наличия поля `fio`
- `login()` / `logout()` управляют сессией

### Core\Role
Система ролей на основе Django groups:
- `getAvailableRoles()` — определяет доступные роли на основе `is_superuser` и group membership
- `getActiveRole()` — активная роль из сессии
- `getVisibleCourseIds()` / `getEditableCourseIds()` — разрешения на курсы
- `getHighestPermission()` — высшее право на конкретный курс

### Core\Csrf
- `token()` — генерирует/возвращает токен из сессии
- `check()` — `hash_equals` для постоянного времени сравнения
- `field()` — HTML hidden input
- JS в footer.php авто-инъектирует токен во все POST-формы

### Core\View
- `render()` — подключает header + view + footer
- `json()` — JSON-ответ с exit

### Core\Flash
- Flash-сообщения через сессию (success/error)

## Слои

### Middleware
- `guest()` — перенаправляет аутентифицированных пользователей
- `auth()` — требует аутентификации
- `teacher()` — требует роль teacher+
- `student()` — требует роль student
- `settingsAccess()` — требует admin (is_superuser)

### Модели
Все модели наследуются от `Model` (app/models/Model.php) и находятся в `app/models/index.php` в одном файле с `namespace Models`.

### Шаблоны
PHP-шаблоны с `htmlspecialchars` для всех выводимых значений. Layout: `layouts/header.php` + `layouts/footer.php`.

## База данных

### Таблицы (группы)
- `auth_user` — сотрудники (Django-style)
- `students_student` — студенты (login, fio, group_id)
- `auth_group` / `auth_user_groups` — группы прав доступа
- `accounts_teachergroup` / `accounts_teachergroup_users` — группы преподавателей
- `structure_university/faculty/department` — организационная структура
- `course_course/section/topic/learningunit` — курсы и содержимое
- `testing_test/question/choice` — тесты
- `chat_chatroom/groupchat/message` — чаты

### Важные связи
- `accounts_teacherprofile_faculties.teacherprofile_id` → `accounts_teacherprofile.id` (НЕ user_id!)
- `course_courseuserpermission` / `course_coursegrouppermission` — права на курсы
