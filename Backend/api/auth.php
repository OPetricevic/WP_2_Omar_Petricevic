<?php
include_once __DIR__ . '/../services/AuthService.php';

// Provjera HTTP metode
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_SERVER['REQUEST_URI'] === '/auth/register') {
    // Učitaj podatke iz body-a
    $input = json_decode(file_get_contents('php://input'), true);

    // Inicijalizuj AuthService
    $authService = new AuthService();
    $response = $authService->register($input);

    // Postavi HTTP status
    http_response_code($response['status']);
    header('Content-Type: application/json');

    // Dodaj status u JSON odgovor
    echo json_encode([
        'status' => $response['status'],
        'message' => $response['message'],
        'user_uuid' => $response['user_uuid'] ?? null
    ]);
    exit;
}

// Ako ruta nije pronađena
http_response_code(404);
echo "Not Found";
?>
