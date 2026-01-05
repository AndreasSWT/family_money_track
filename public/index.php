<?php

declare(strict_types=1);

require __DIR__ . '/../src/bootstrap.php';

use App\Controllers\AuthController;
use App\Controllers\HouseholdController;
use App\Controllers\InviteController;
use App\Support\Router;

$router = new Router();

$router->post('/api/auth/register', [AuthController::class, 'register']);
$router->post('/api/auth/login', [AuthController::class, 'login']);
$router->post('/api/auth/logout', [AuthController::class, 'logout']);
$router->get('/api/auth/me', [AuthController::class, 'me']);

$router->post('/api/households', [HouseholdController::class, 'create']);
$router->get('/api/households', [HouseholdController::class, 'index']);
$router->get('/api/households/{id}', [HouseholdController::class, 'show']);
$router->get('/api/households/{id}/members', [HouseholdController::class, 'members']);
$router->patch('/api/households/{id}/members/{userId}', [HouseholdController::class, 'updateMemberRole']);
$router->delete('/api/households/{id}/members/{userId}', [HouseholdController::class, 'removeMember']);

$router->post('/api/households/{id}/invites', [InviteController::class, 'create']);
$router->post('/api/invites/{code}/accept', [InviteController::class, 'accept']);

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';

$response = $router->dispatch($method, $path);
$response->send();
