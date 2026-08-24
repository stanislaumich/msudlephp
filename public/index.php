<?php

    $GLOBALS['config'] = require_once __DIR__ . '/../app/config/config.php';
    $config = $GLOBALS['config'];

if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_samesite', 'Lax');
    if (APP_ENV === 'production') {
        ini_set('session.cookie_secure', 1);
    }
    session_name($config['session']['name']);
    session_set_cookie_params([
        'lifetime' => $config['session']['lifetime'],
        'httponly' => true,
        'samesite' => 'Lax',
        'secure' => APP_ENV === 'production',
    ]);
    session_start();
}

spl_autoload_register(function ($class) {
    $prefix = 'Controllers\\';
    $baseDir = APP_PATH . '/controllers/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) return;

    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
    if (file_exists($file)) {
        require $file;
    }
});

spl_autoload_register(function ($class) {
    if (strpos($class, 'Models\\') === 0) {
        $file = APP_PATH . '/models/' . substr($class, 7) . '.php';
        if (file_exists($file)) require $file;
    }
});

spl_autoload_register(function ($class) {
    if (strpos($class, 'Core\\') === 0) {
        $file = APP_PATH . '/core/' . substr($class, 5) . '.php';
        if (file_exists($file)) require $file;
    }
});

if ($config['app']['debug']) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

$logDir = STORAGE_PATH . '/logs';
if (!is_dir($logDir)) {
    mkdir($logDir, 0777, true);
}
ini_set('log_errors', 1);
ini_set('error_log', $logDir . '/php_errors.log');

require_once APP_PATH . '/core/Csrf.php';
require_once APP_PATH . '/middleware/Middleware.php';
require_once APP_PATH . '/models/index.php';

if (in_array($_SERVER['REQUEST_METHOD'] ?? 'GET', ['POST', 'PUT', 'DELETE', 'PATCH'])) {
    $submittedToken = $_POST['csrf_token'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? null);
    if (!\Core\Csrf::check($submittedToken)) {
        http_response_code(419);
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            \Core\View::json(['error' => 'CSRF token mismatch'], 419);
        } else {
            echo '419 — Page Expired (CSRF token mismatch)';
        }
        exit;
    }
}

require_once APP_PATH . '/routes/web.php';
