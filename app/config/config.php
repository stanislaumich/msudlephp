<?php

$envPath = dirname(__DIR__, 2) . '/.env';
if (file_exists($envPath)) {
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lines !== false) {
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || strpos($line, '#') === 0) continue;
        $parts = explode('=', $line, 2);
        if (count($parts) === 2) {
            $key = trim($parts[0]);
            $value = trim($parts[1]);
            if (!isset($_ENV[$key])) $_ENV[$key] = $value;
            if (!getenv($key)) putenv("{$key}={$value}");
        }
    }
    }
}

define('APP_PATH', dirname(__DIR__));
define('PUBLIC_PATH', APP_PATH . '/public');
define('STORAGE_PATH', APP_PATH . '/storage');
define('DATA_PATH', STORAGE_PATH . '/data');
define('LOG_PATH', STORAGE_PATH . '/logs');
define('APP_ENV', getenv('APP_ENV') ?: 'production');
define('APP_DEBUG', (getenv('APP_DEBUG') ?: 'false') === 'true');

$config = [
    'app' => [
        'name' => getenv('APP_NAME') ?: 'MSUDLE',
        'env' => APP_ENV,
        'debug' => APP_DEBUG,
        'url' => getenv('APP_URL') ?: 'http://localhost',
    ],
    'db' => [
        'driver' => getenv('DB_DRIVER') ?: 'mysql',
        'host' => getenv('DB_HOST') ?: 'localhost',
        'port' => getenv('DB_PORT') ?: 3306,
        'name' => getenv('DB_NAME') ?: 'msudle',
        'user' => getenv('DB_USER') ?: 'root',
        'pass' => getenv('DB_PASS') ?: '',
        'charset' => 'utf8mb4',
    ],
    'session' => [
        'name' => getenv('SESSION_NAME') ?: 'msudle_session',
        'lifetime' => (int)(getenv('SESSION_LIFETIME') ?: 86400),
    ],
    'paths' => [
        'root' => APP_PATH,
        'public' => PUBLIC_PATH,
        'app' => APP_PATH,
        'storage' => STORAGE_PATH,
        'uploads' => PUBLIC_PATH . '/uploads',
        'data' => DATA_PATH,
    ],
];

$GLOBALS['config'] = $config;
return $config;
