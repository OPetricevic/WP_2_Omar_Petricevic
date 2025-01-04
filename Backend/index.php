<?php
// Osnovni fajl koji rutira sve zahtjeve prema odgovarajućim endpointima

$requestUri = $_SERVER['REQUEST_URI'];
$method = $_SERVER['REQUEST_METHOD'];

// Rutiranje prema endpointima
if (strpos($requestUri, '/auth') === 0) {
    include_once __DIR__ . '/api/auth.php';
} elseif (strpos($requestUri, '/news') === 0) {
    include_once __DIR__ . '/api/news.php';
} elseif (strpos($requestUri, '/users') === 0) {
    include_once __DIR__ . '/api/users.php';
} elseif (strpos($requestUri, '/roles') === 0) { 
    include_once __DIR__ . '/api/roles.php';
} else {
    // Ako ruta nije pronađena
    http_response_code(404);
    echo json_encode(['message' => 'Endpoint not found']);
}
?>
