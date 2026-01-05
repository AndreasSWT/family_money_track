<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Auth\Auth;
use App\Support\Request;
use App\Support\Response;

final class AuthController
{
    public function register(Request $request): Response
    {
        $email = strtolower(trim((string) $request->input('email', '')));
        $password = (string) $request->input('password', '');
        $displayName = trim((string) $request->input('display_name', ''));

        $errors = $this->validateAuthInput($email, $password, $displayName);
        if ($errors) {
            return Response::json(['error' => 'validation_error', 'fields' => $errors], 422);
        }

        $pdo = db();
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            return Response::error('Email already registered', 409, 'email_exists');
        }

        $hash = password_hash($password, PASSWORD_DEFAULT);
        $insert = $pdo->prepare('INSERT INTO users (email, password_hash, display_name, created_at) VALUES (?, ?, ?, NOW())');
        $insert->execute([$email, $hash, $displayName]);

        $userId = (int) $pdo->lastInsertId();
        $csrf = Auth::login($userId);

        return Response::json([
            'user' => [
                'id' => $userId,
                'email' => $email,
                'display_name' => $displayName,
            ],
            'csrf_token' => $csrf,
        ], 201);
    }

    public function login(Request $request): Response
    {
        $email = strtolower(trim((string) $request->input('email', '')));
        $password = (string) $request->input('password', '');

        if ($email === '' || $password === '') {
            return Response::json(['error' => 'validation_error'], 422);
        }

        $pdo = db();
        $stmt = $pdo->prepare('SELECT id, email, password_hash, display_name FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password_hash'])) {
            return Response::error('Invalid credentials', 401, 'invalid_credentials');
        }

        $csrf = Auth::login((int) $user['id']);

        return Response::json([
            'user' => [
                'id' => (int) $user['id'],
                'email' => $user['email'],
                'display_name' => $user['display_name'],
            ],
            'csrf_token' => $csrf,
        ]);
    }

    public function logout(Request $request): Response
    {
        $userId = Auth::userId();
        if (!$userId) {
            return Response::json(['status' => 'ok']);
        }

        Auth::requireUser($request);
        Auth::logout();

        return Response::json(['status' => 'ok']);
    }

    public function me(Request $request): Response
    {
        $userId = Auth::userId();
        if (!$userId) {
            return Response::error('Unauthorized', 401, 'unauthorized');
        }

        $pdo = db();
        $stmt = $pdo->prepare('SELECT id, email, display_name FROM users WHERE id = ? LIMIT 1');
        $stmt->execute([$userId]);
        $user = $stmt->fetch();

        if (!$user) {
            Auth::logout();
            return Response::error('Unauthorized', 401, 'unauthorized');
        }

        return Response::json([
            'user' => [
                'id' => (int) $user['id'],
                'email' => $user['email'],
                'display_name' => $user['display_name'],
            ],
            'csrf_token' => $_SESSION['csrf_token'] ?? null,
        ]);
    }

    private function validateAuthInput(string $email, string $password, string $displayName): array
    {
        $errors = [];

        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'invalid';
        }

        if (strlen($password) < 8) {
            $errors['password'] = 'min_8';
        }

        if ($displayName === '') {
            $errors['display_name'] = 'required';
        }

        return $errors;
    }
}
