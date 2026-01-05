<?php

declare(strict_types=1);

namespace App\Auth;

use App\Support\Request;

final class Auth
{
    public static function userId(): ?int
    {
        return isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;
    }

    public static function requireUser(Request $request): int
    {
        $userId = self::userId();
        if (!$userId) {
            throw new \RuntimeException('unauthenticated');
        }

        self::requireCsrf($request);

        return $userId;
    }

    public static function login(int $userId): string
    {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $userId;
        $_SESSION['csrf_token'] = bin2hex(random_bytes(16));

        return $_SESSION['csrf_token'];
    }

    public static function logout(): void
    {
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }

        session_destroy();
    }

    private static function requireCsrf(Request $request): void
    {
        if (in_array($request->method(), ['POST', 'PATCH', 'DELETE'], true)) {
            $token = $request->header('x-csrf-token');
            $sessionToken = $_SESSION['csrf_token'] ?? null;

            if (!$sessionToken || !$token || !hash_equals($sessionToken, $token)) {
                throw new \RuntimeException('csrf');
            }
        }
    }
}
