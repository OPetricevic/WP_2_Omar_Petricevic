<?php
include_once __DIR__ . '/../services/RoleService.php';
include_once __DIR__ . '/../Middleware/AuthMiddleware.php';
include_once __DIR__ . '/../Middleware/RoleMiddleware.php';

// Inicijalizacija middleware-a
$authMiddleware = new AuthMiddleware();
$roleMiddleware = new RoleMiddleware();

if ($_SERVER['REQUEST_METHOD'] === 'PATCH' && $_SERVER['REQUEST_URI'] === '/roles/change') {
    // Validacija JWT tokena
    $decodedToken = $authMiddleware->requireAuth(); // Osigurava da je korisnik prijavljen

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
        $roleService = new RoleService();
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

http_response_code(404);
echo json_encode(['message' => 'Endpoint not found.']);
?>
