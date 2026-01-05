<?php

declare(strict_types=1);

use App\Support\Env;

spl_autoload_register(function (string $class): void {
    if (strncmp($class, 'App\\', 4) !== 0) {
        return;
    }

    $relative = substr($class, 4);
    $path = __DIR__ . '/' . str_replace('\\', '/', $relative) . '.php';

    if (is_file($path)) {
        require $path;
    }
});

Env::load(__DIR__ . '/../.env');

$timezone = env('APP_TIMEZONE', 'Europe/Budapest');
if ($timezone) {
    date_default_timezone_set($timezone);
}

$debug = env('APP_ENV', 'production') !== 'production';
ini_set('display_errors', $debug ? '1' : '0');
ini_set('display_startup_errors', $debug ? '1' : '0');

$secure = (($_SERVER['HTTPS'] ?? '') === 'on');

session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => '',
    'secure' => $secure,
    'httponly' => true,
    'samesite' => 'Lax',
]);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$dsn = sprintf(
    'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
    env('DB_HOST', '127.0.0.1'),
    env('DB_PORT', '3306'),
    env('DB_NAME', 'family_money_track')
);

$pdo = new PDO($dsn, env('DB_USER', 'root'), env('DB_PASS', ''), [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
]);

$GLOBALS['app_db'] = $pdo;

function env(string $key, ?string $default = null): ?string
{
    if (array_key_exists($key, $_ENV)) {
        return $_ENV[$key];
    }

    if (array_key_exists($key, $_SERVER)) {
        return $_SERVER[$key];
    }

    return $default;
}

function db(): PDO
{
    return $GLOBALS['app_db'];
}
