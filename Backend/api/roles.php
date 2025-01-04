<?php
include_once __DIR__ . '/../services/RoleService.php';

if ($_SERVER['REQUEST_METHOD'] === 'PATCH' && $_SERVER['REQUEST_URI'] === '/roles/change') {
    // Učitavanje podataka iz zahteva
    $data = json_decode(file_get_contents('php://input'), true);

    // Provera da li su svi potrebni podaci poslati
    if (empty($data['user_uuid']) || empty($data['new_role'])) {
        http_response_code(400);
        echo json_encode(['message' => 'Missing required fields: user_uuid or new_role']);
        exit;
    }

    try {
        // Pozivanje servisa za promenu role
        $roleService = new RoleService();
        $response = $roleService->changeRole($data['user_uuid'], $data['new_role']);

        // Postavljanje HTTP status koda i vraćanje odgovora
        http_response_code($response['status']);
        echo json_encode([
            'status' => $response['status'],
            'message' => $response['message'],
            'user_uuid' => $data['user_uuid'],
            'new_role' => $data['new_role']
        ], JSON_PRETTY_PRINT);
    } catch (Exception $e) {
        // Upravljanje greškom
        http_response_code(500);
        echo json_encode([
            'status' => 500,
            'message' => 'Internal server error.',
            'error' => $e->getMessage()
        ], JSON_PRETTY_PRINT);
    }

    exit;
}

// Ako ruta nije pronađena
http_response_code(404);
echo json_encode(['message' => 'Endpoint not found.']);
?>
