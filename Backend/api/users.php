<?php

include_once __DIR__ . '/../services/UserService.php';
include_once __DIR__ . '/../middleware/AuthMiddleware.php';

// Initialize the services and middleware
$userService = new UserService();
$authMiddleware = new AuthMiddleware();

// GET: Fetch the currently logged-in user
if ($_SERVER['REQUEST_METHOD'] === 'GET' && $_SERVER['REQUEST_URI'] === '/users/me') {
    $decodedToken = $authMiddleware->requireAuth(); // Validate JWT
    try {
        $user = $userService->getUserByUuid($decodedToken->uuid);
        if (!$user) {
            http_response_code(404);
            echo json_encode(['message' => 'User not found']);
            exit;
        }

        // Mapiranje role
        $roles = [
            1 => 'User',
            2 => 'Creator',
            3 => 'Admin'
        ];
        $user['role_name'] = $roles[$user['role']] ?? 'Unknown';

        http_response_code(200);
        echo json_encode($user, JSON_PRETTY_PRINT);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Internal server error']);
    }
    exit;
}

// PATCH: Update the currently logged-in user's email or username
if ($_SERVER['REQUEST_METHOD'] === 'PATCH' && $_SERVER['REQUEST_URI'] === '/users/me') {
    $decodedToken = $authMiddleware->requireAuth(); // Validate JWT
    $data = json_decode(file_get_contents('php://input'), true);

    if (empty($data['email']) && empty($data['username'])) {
        http_response_code(400);
        echo json_encode(['message' => 'No fields to update']);
        exit;
    }

    try {
        $response = $userService->updateUser($decodedToken->uuid, $data);
        http_response_code($response['status']);
        echo json_encode($response, JSON_PRETTY_PRINT);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Internal server error']);
    }
    exit;
}

// DELETE: Delete the currently logged-in user's account
if ($_SERVER['REQUEST_METHOD'] === 'DELETE' && $_SERVER['REQUEST_URI'] === '/users/me') {
    $decodedToken = $authMiddleware->requireAuth(); // Validate JWT
    try {
        $response = $userService->deleteUser($decodedToken->uuid);
        http_response_code($response['status']);
        echo json_encode($response, JSON_PRETTY_PRINT);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Internal server error']);
    }
    exit;
}

// Default 404
http_response_code(404);
echo json_encode(['message' => 'Endpoint not found']);
?>
