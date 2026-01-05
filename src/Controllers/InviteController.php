<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Auth\Auth;
use App\Support\Request;
use App\Support\Response;
use DateTimeImmutable;

final class InviteController
{
    public function create(Request $request, string $id): Response
    {
        $userId = Auth::requireUser($request);
        $householdId = (int) $id;

        $pdo = db();
        $role = (string) $request->input('role', 'editor');
        if (!in_array($role, ['editor', 'viewer'], true)) {
            return Response::json(['error' => 'validation_error', 'fields' => ['role' => 'invalid']], 422);
        }

        $membership = $this->membership($householdId, $userId);
        if (!$membership) {
            return Response::error('Not found', 404, 'not_found');
        }

        if ($membership['role'] !== 'owner') {
            throw new \RuntimeException('forbidden');
        }

        $expiresAt = $this->parseExpiresAt((string) $request->input('expires_at', ''));
        if ($expiresAt === false) {
            return Response::json(['error' => 'validation_error', 'fields' => ['expires_at' => 'invalid']], 422);
        }

        $code = bin2hex(random_bytes(16));

        $stmt = $pdo->prepare(
            'INSERT INTO household_invites (household_id, invite_code, role, expires_at, created_by_user_id, created_at)
             VALUES (?, ?, ?, ?, ?, NOW())'
        );
        $stmt->execute([$householdId, $code, $role, $expiresAt, $userId]);

        return Response::json([
            'invite_code' => $code,
        ], 201);
    }

    public function accept(Request $request, string $code): Response
    {
        $userId = Auth::requireUser($request);
        $inviteCode = trim($code);

        $pdo = db();
        $stmt = $pdo->prepare('SELECT * FROM household_invites WHERE invite_code = ? LIMIT 1');
        $stmt->execute([$inviteCode]);
        $invite = $stmt->fetch();

        if (!$invite) {
            return Response::error('Invite not found', 404, 'not_found');
        }

        if ($invite['used_at'] !== null || $invite['used_by_user_id'] !== null) {
            return Response::error('Invite already used', 409, 'invite_used');
        }

        if ($invite['expires_at'] !== null && strtotime($invite['expires_at']) < time()) {
            return Response::error('Invite expired', 410, 'invite_expired');
        }

        $existing = $this->membership((int) $invite['household_id'], $userId);
        if ($existing) {
            return Response::error('Already a member', 409, 'already_member');
        }

        try {
            $pdo->beginTransaction();

            $insert = $pdo->prepare('INSERT INTO household_members (household_id, user_id, role, joined_at) VALUES (?, ?, ?, NOW())');
            $insert->execute([(int) $invite['household_id'], $userId, $invite['role']]);

            $update = $pdo->prepare('UPDATE household_invites SET used_by_user_id = ?, used_at = NOW() WHERE id = ?');
            $update->execute([$userId, (int) $invite['id']]);

            $pdo->commit();
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }

        return Response::json(['status' => 'ok']);
    }

    private function membership(int $householdId, int $userId): ?array
    {
        $pdo = db();
        $stmt = $pdo->prepare(
            'SELECT hm.role
             FROM household_members hm
             WHERE hm.household_id = ? AND hm.user_id = ?'
        );
        $stmt->execute([$householdId, $userId]);
        $row = $stmt->fetch();

        return $row ?: null;
    }

    private function parseExpiresAt(string $value): string|false|null
    {
        $trimmed = trim($value);
        if ($trimmed === '') {
            return null;
        }

        $date = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $trimmed)
            ?: DateTimeImmutable::createFromFormat(DateTimeImmutable::ATOM, $trimmed);

        if (!$date) {
            return false;
        }

        return $date->format('Y-m-d H:i:s');
    }
}
