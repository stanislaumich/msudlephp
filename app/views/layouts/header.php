
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MSUDLE</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <script>
        window.csrfToken = <?php echo json_encode(\Core\Csrf::token()); ?>;
    </script>
    <meta name="csrf-token" content="<?php echo \Core\Csrf::token(); ?>">
    <style>
        .sidebar { min-height: 100vh; }
        .nav-link { color: #fff; }
        .nav-link:hover, .nav-link.active { background: rgba(255,255,255,0.1); }
        .card-stat { transition: transform 0.2s; }
        .card-stat:hover { transform: translateY(-3px); }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <?php if (Core\Auth::check() && Core\Auth::guard() === 'teacher'): ?>
            <nav class="col-md-2 d-none d-md-block bg-dark sidebar">
                <div class="position-sticky pt-3">
                    <h5 class="text-white px-3 mb-3">MSUDLE</h5>
                    <ul class="nav flex-column">
                        <li class="nav-item"><a class="nav-link <?php echo ($_SERVER['REQUEST_URI'] == '/home' || $_SERVER['REQUEST_URI'] == '/dashboard') ? 'active' : ''; ?>" href="/home"><i class="bi bi-house-door me-2"></i>Главная</a></li>
                        <li class="nav-item"><a class="nav-link <?php echo strpos($_SERVER['REQUEST_URI'], '/courses') === 0 ? 'active' : ''; ?>" href="/courses/"><i class="bi bi-book me-2"></i>Курсы</a></li>
                        <li class="nav-item"><a class="nav-link <?php echo strpos($_SERVER['REQUEST_URI'], '/students') === 0 ? 'active' : ''; ?>" href="/students/"><i class="bi bi-people me-2"></i>Студенты</a></li>
                        <li class="nav-item"><a class="nav-link <?php echo strpos($_SERVER['REQUEST_URI'], '/structure') === 0 ? 'active' : ''; ?>" href="/structure/"><i class="bi bi-building me-2"></i>Структура</a></li>
                        <li class="nav-item"><a class="nav-link <?php echo strpos($_SERVER['REQUEST_URI'], '/subjects') === 0 ? 'active' : ''; ?>" href="/subjects/"><i class="bi bi-journal-text me-2"></i>Дисциплины</a></li>
                        <li class="nav-item"><a class="nav-link <?php echo strpos($_SERVER['REQUEST_URI'], '/course-types') === 0 ? 'active' : ''; ?>" href="/course-types/"><i class="bi bi-tags me-2"></i>Типы курсов</a></li>
                        <li class="nav-item"><a class="nav-link <?php echo strpos($_SERVER['REQUEST_URI'], '/umo') === 0 ? 'active' : ''; ?>" href="/umo/"><i class="bi bi-card-list me-2"></i>УМО</a></li>
                        <li class="nav-item"><a class="nav-link <?php echo strpos($_SERVER['REQUEST_URI'], '/testing') === 0 ? 'active' : ''; ?>" href="/testing/"><i class="bi bi-clipboard-check me-2"></i>Тесты</a></li>
                        <li class="nav-item"><a class="nav-link <?php echo strpos($_SERVER['REQUEST_URI'], '/chat') === 0 ? 'active' : ''; ?>" href="/chat/"><i class="bi bi-chat-dots me-2"></i>Чат</a></li>
                        <?php if ($role && \Core\Role::canManageSettings($user ?? [], $role)): ?>
                        <li class="nav-item"><a class="nav-link <?php echo strpos($_SERVER['REQUEST_URI'], '/accounts') === 0 ? 'active' : ''; ?>" href="/accounts/"><i class="bi bi-person-gear me-2"></i>Преподаватели</a></li>
                        <?php endif; ?>
                        <?php if (isset($_SESSION['impersonate_original_admin_id'])): ?>
                        <li class="nav-item mt-3">
                            <div class="alert alert-warning mx-3 mb-0 p-2 text-center">
                                <i class="bi bi-person-badge me-1"></i>Вы вошли как студент
                                <form method="POST" action="/stop-impersonation/" class="d-inline">
                                    <input type="hidden" name="csrf_token" value="<?php echo \Core\Csrf::token(); ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-dark mt-1">Вернуться к админу</button>
                                </form>
                            </div>
                        </li>
                        <?php endif; ?>
                        <li class="nav-item mt-3">
                            <form method="POST" action="/set-role" class="px-3">
                                <input type="hidden" name="csrf_token" value="<?php echo \Core\Csrf::token(); ?>">
                                <select name="role" class="form-select form-select-sm bg-dark text-white mb-2" onchange="this.form.submit()">
                                    <?php foreach ($available_roles ?? [] as $r): ?>
                                    <option value="<?php echo $r; ?>" <?php echo ($active_role ?? '') === $r ? 'selected' : ''; ?>><?php echo \Core\Role::label($r); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </form>
                        </li>
                        <li class="nav-item"><a class="nav-link text-warning" href="/logout/"><i class="bi bi-box-arrow-right me-2"></i>Выход</a></li>
                    </ul>
                </div>
            </nav>
            <?php endif; ?>
            <main class="col-md-10 ms-sm-auto col-lg-10 px-md-4 py-4 <?php echo (Core\Auth::check() && Core\Auth::guard() === 'teacher') ? '' : 'col-12'; ?>">
                <?php if (isset($_SESSION['messages'])): foreach ($_SESSION['messages'] as $type => $msgs): foreach ($msgs as $msg): ?>
                <div class="alert alert-<?php echo $type; ?> alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($msg); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endforeach; endforeach; unset($_SESSION['messages']); endif; ?>
