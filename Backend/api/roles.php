<?php
include_once __DIR__ . '/../services/RoleService.php';
include_once __DIR__ . '/../middleware/AuthMiddleware.php';
include_once __DIR__ . '/../middleware/RoleMiddleware.php';

// Inicijalizacija middleware-a
$authMiddleware = new AuthMiddleware();
$roleMiddleware = new RoleMiddleware();
$roleService = new RoleService();

// Endpoint for getting pending role change requests
if ($_SERVER['REQUEST_METHOD'] === 'GET' && $_SERVER['REQUEST_URI'] === '/roles/requests') {
    // Validate JWT token
    $decodedToken = $authMiddleware->requireAuth(); 

    // Check role permissions
    $roleMiddleware->requireRole($decodedToken, [3]); // Only admins can access this

    try {
        $requests = $roleService->getPendingRoleChangeRequests();
        http_response_code(200);
        echo json_encode($requests, JSON_PRETTY_PRINT);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'status' => 500,
            'message' => 'Internal server error.',
            'error' => $e->getMessage()
        ], JSON_PRETTY_PRINT);
    }

    exit;
}

// Endpoint for changing roles
if ($_SERVER['REQUEST_METHOD'] === 'PATCH' && $_SERVER['REQUEST_URI'] === '/roles/change') {
    // Validacija JWT tokena
    $decodedToken = $authMiddleware->requireAuth();

    // Provjera uloge
    $roleMiddleware->requireRole($decodedToken, [3]); // Dozvoljeno samo adminima (rola 3)

    $data = json_decode(file_get_contents('php://input'), true);

    // Provjera potrebnih polja
    if (empty($data['user_uuid']) || !isset($data['new_role'])) {
        http_response_code(400);
        echo json_encode(['message' => 'Missing required fields: user_uuid or new_role']);
        exit;
    }

    // Validacija tipa za `new_role`
    if (!is_int($data['new_role'])) {
        http_response_code(400);
        echo json_encode(['message' => 'Invalid new_role type. Must be an integer.']);
        exit;
    }

    try {
        $response = $roleService->changeRole($data['user_uuid'], $data['new_role']);
        http_response_code($response['status']);
        echo json_encode([
            'status' => $response['status'],
            'message' => $response['message'],
            'user_uuid' => $data['user_uuid'],
            'new_role' => $data['new_role']
        ], JSON_PRETTY_PRINT);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'status' => 500,
            'message' => 'Internal server error.',
            'error' => $e->getMessage()
        ], JSON_PRETTY_PRINT);
    }

    exit;
}

// Endpoint for requesting role changes
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_SERVER['REQUEST_URI'] === '/roles/request-change') {
    // Validacija JWT tokena
    $decodedToken = $authMiddleware->requireAuth();

    $data = json_decode(file_get_contents('php://input'), true);

    // Provjera potrebnih polja
    if (empty($data['requested_role'])) {
        http_response_code(400);
        echo json_encode(['message' => 'Missing required field: requested_role']);
        exit;
    }

    // Validacija tipa za `requested_role`
    if (!is_int($data['requested_role'])) {
        http_response_code(400);
        echo json_encode(['message' => 'Invalid requested_role type. Must be an integer.']);
        exit;
    }

    try {
        $response = $roleService->submitRoleChangeRequest($decodedToken->uuid, $data['requested_role']);
        http_response_code($response['status']);
        echo json_encode($response, JSON_PRETTY_PRINT);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'status' => 500,
            'message' => 'Internal server error.',
            'error' => $e->getMessage()
        ], JSON_PRETTY_PRINT);
    }

    exit;
}

// Endpoint for reviewing requests (approve/reject)
if ($_SERVER['REQUEST_METHOD'] === 'PATCH' && $_SERVER['REQUEST_URI'] === '/roles/review-request') {
    // Validacija JWT tokena
    $decodedToken = $authMiddleware->requireAuth();

    // Provjera uloge
    $roleMiddleware->requireRole($decodedToken, [3]);

    $data = json_decode(file_get_contents('php://input'), true);

    // Provjera potrebnih polja
    if (empty($data['request_uuid']) || empty($data['action'])) {
        http_response_code(400);
        echo json_encode(['message' => 'Missing required fields: request_uuid or action']);
        exit;
    }

    // Validacija tipa za `action`
    $validActions = ['approve', 'reject'];
    if (!in_array($data['action'], $validActions)) {
        http_response_code(400);
        echo json_encode(['message' => 'Invalid action. Must be "approve" or "reject".']);
        exit;
    }

    try {
        $response = $roleService->reviewRoleChangeRequest($data['request_uuid'], $data['action']);
        http_response_code($response['status']);
        echo json_encode($response, JSON_PRETTY_PRINT);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'status' => 500,
            'message' => 'Internal server error.',
            'error' => $e->getMessage()
        ], JSON_PRETTY_PRINT);
    }

    exit;
}

http_response_code(404);
echo json_encode(['message' => 'Endpoint not found.']);
?>
