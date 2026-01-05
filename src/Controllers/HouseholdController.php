<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Auth\Auth;
use App\Support\Request;
use App\Support\Response;

final class HouseholdController
{
    public function create(Request $request): Response
    {
        $userId = Auth::requireUser($request);
        $name = trim((string) $request->input('name', ''));

        if ($name === '') {
            return Response::json(['error' => 'validation_error', 'fields' => ['name' => 'required']], 422);
        }

        $pdo = db();

        try {
            $pdo->beginTransaction();

            $insert = $pdo->prepare('INSERT INTO households (name, currency_code, owner_user_id, created_at) VALUES (?, ?, ?, NOW())');
            $insert->execute([$name, env('APP_CURRENCY', 'HUF'), $userId]);
            $householdId = (int) $pdo->lastInsertId();

            $member = $pdo->prepare('INSERT INTO household_members (household_id, user_id, role, joined_at) VALUES (?, ?, ?, NOW())');
            $member->execute([$householdId, $userId, 'owner']);

            $pdo->commit();
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }

        return Response::json([
            'household' => [
                'id' => $householdId,
                'name' => $name,
                'currency_code' => env('APP_CURRENCY', 'HUF'),
                'role' => 'owner',
            ],
        ], 201);
    }

    public function index(Request $request): Response
    {
        $userId = Auth::requireUser($request);

        $pdo = db();
        $stmt = $pdo->prepare(
            'SELECT h.id, h.name, h.currency_code, h.owner_user_id, hm.role
             FROM households h
             JOIN household_members hm ON hm.household_id = h.id
             WHERE hm.user_id = ?
             ORDER BY h.id DESC'
        );
        $stmt->execute([$userId]);
        $rows = $stmt->fetchAll();

        return Response::json(['households' => $rows]);
    }

    public function show(Request $request, string $id): Response
    {
        $userId = Auth::requireUser($request);
        $householdId = (int) $id;

        $membership = $this->membership($householdId, $userId);
        if (!$membership) {
            return Response::error('Not found', 404, 'not_found');
        }

        $pdo = db();
        $stmt = $pdo->prepare('SELECT id, name, currency_code, owner_user_id FROM households WHERE id = ?');
        $stmt->execute([$householdId]);
        $household = $stmt->fetch();

        return Response::json([
            'household' => $household,
            'role' => $membership['role'],
        ]);
    }

    public function members(Request $request, string $id): Response
    {
        $userId = Auth::requireUser($request);
        $householdId = (int) $id;

        $membership = $this->membership($householdId, $userId);
        if (!$membership) {
            return Response::error('Not found', 404, 'not_found');
        }

        $pdo = db();
        $stmt = $pdo->prepare(
            'SELECT u.id, u.email, u.display_name, hm.role, hm.joined_at
             FROM household_members hm
             JOIN users u ON u.id = hm.user_id
             WHERE hm.household_id = ?
             ORDER BY hm.joined_at ASC'
        );
        $stmt->execute([$householdId]);
        $members = $stmt->fetchAll();

        return Response::json(['members' => $members]);
    }

    public function updateMemberRole(Request $request, string $id, string $userId): Response
    {
        $currentUserId = Auth::requireUser($request);
        $householdId = (int) $id;
        $targetUserId = (int) $userId;

        $membership = $this->membership($householdId, $currentUserId);
        if (!$membership) {
            return Response::error('Not found', 404, 'not_found');
        }

        if ($membership['role'] !== 'owner') {
            throw new \RuntimeException('forbidden');
        }

        $role = (string) $request->input('role', '');
        if (!in_array($role, ['editor', 'viewer'], true)) {
            return Response::json(['error' => 'validation_error', 'fields' => ['role' => 'invalid']], 422);
        }

        $pdo = db();
        $stmt = $pdo->prepare('SELECT owner_user_id FROM households WHERE id = ?');
        $stmt->execute([$householdId]);
        $household = $stmt->fetch();

        if (!$household) {
            return Response::error('Not found', 404, 'not_found');
        }

        if ((int) $household['owner_user_id'] === $targetUserId) {
            return Response::error('Owner role cannot be changed', 409, 'owner_locked');
        }

        $update = $pdo->prepare('UPDATE household_members SET role = ? WHERE household_id = ? AND user_id = ?');
        $update->execute([$role, $householdId, $targetUserId]);

        if ($update->rowCount() === 0) {
            return Response::error('Member not found', 404, 'not_found');
        }

        return Response::json(['status' => 'ok']);
    }

    public function removeMember(Request $request, string $id, string $userId): Response
    {
        $currentUserId = Auth::requireUser($request);
        $householdId = (int) $id;
        $targetUserId = (int) $userId;

        $membership = $this->membership($householdId, $currentUserId);
        if (!$membership) {
            return Response::error('Not found', 404, 'not_found');
        }

        if ($membership['role'] !== 'owner') {
            throw new \RuntimeException('forbidden');
        }

        $pdo = db();
        $stmt = $pdo->prepare('SELECT owner_user_id FROM households WHERE id = ?');
        $stmt->execute([$householdId]);
        $household = $stmt->fetch();

        if (!$household) {
            return Response::error('Not found', 404, 'not_found');
        }

        if ((int) $household['owner_user_id'] === $targetUserId) {
            return Response::error('Owner cannot be removed', 409, 'owner_locked');
        }

        $delete = $pdo->prepare('DELETE FROM household_members WHERE household_id = ? AND user_id = ?');
        $delete->execute([$householdId, $targetUserId]);

        if ($delete->rowCount() === 0) {
            return Response::error('Member not found', 404, 'not_found');
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
}
