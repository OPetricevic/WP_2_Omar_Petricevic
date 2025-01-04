<?php
include_once __DIR__ . '/../middleware/AuthMiddleware.php';
include_once __DIR__ . '/../middleware/RoleMiddleware.php';
include_once __DIR__ . '/../services/ImageService.php';

// Middleware za autentifikaciju
$authMiddleware = new AuthMiddleware();
$decodedToken = $authMiddleware->requireAuth();

// Middleware za uloge
$roleMiddleware = new RoleMiddleware();

// Servis za rad sa slikama
$imageService = new ImageService();

// Endpoint za kreiranje slike
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_SERVER['REQUEST_URI'] === '/images') {
    // Provjera minimalne uloge: user
    $roleMiddleware->requireRole($decodedToken, 1);

    $input = json_decode(file_get_contents('php://input'), true);

    // Validacija unosa
    if (empty($input['module_uuid']) || empty($input['url'])) {
        http_response_code(400);
        echo json_encode(['message' => 'module_uuid and url are required.']);
        exit;
    }

    // Brisanje stare slike (ako postoji)
    $imageService->deleteOldImage($input['module_uuid']);

    // Dodavanje nove slike
    $response = $imageService->addImage([
        'module_uuid' => $input['module_uuid'],
        'url' => $input['url'],
        'description' => $input['description'] ?? null
    ]);

    // Postavljanje odgovora
    http_response_code($response['status']);
    echo json_encode(['message' => $response['message']]);
    exit;
}

// Endpoint za dobijanje svih slika vezanih za module_uuid
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['module_uuid'])) {
    // Provjera minimalne uloge: user
    $roleMiddleware->requireRole($decodedToken, 1);

    $moduleUuid = $_GET['module_uuid'];

    $images = $imageService->getAllImages($moduleUuid);

    http_response_code(200);
    echo json_encode(['images' => $images]);
    exit;
}

// Endpoint za dobijanje jedne slike prema uuid
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['uuid'])) {
    // Provjera minimalne uloge: user
    $roleMiddleware->requireRole($decodedToken, 1);

    $uuid = $_GET['uuid'];

    $image = $imageService->getImageByUuid($uuid);

    if (!$image) {
        http_response_code(404);
        echo json_encode(['message' => 'Image not found.']);
        exit;
    }

    http_response_code(200);
    echo json_encode(['image' => $image]);
    exit;
}

// Endpoint za ažuriranje opisa slike
if ($_SERVER['REQUEST_METHOD'] === 'PATCH' && isset($_GET['uuid'])) {
    // Provjera minimalne uloge: user
    $roleMiddleware->requireRole($decodedToken, 1);

    $uuid = $_GET['uuid'];
    $input = json_decode(file_get_contents('php://input'), true);

    if (empty($input['description'])) {
        http_response_code(400);
        echo json_encode(['message' => 'Description is required.']);
        exit;
    }

    $response = $imageService->updateImageDescription($uuid, $input['description']);

    http_response_code($response['status']);
    echo json_encode(['message' => $response['message']]);
    exit;
}

// Endpoint za brisanje slike prema uuid
if ($_SERVER['REQUEST_METHOD'] === 'DELETE' && isset($_GET['uuid'])) {
    // Provjera minimalne uloge: user
    $roleMiddleware->requireRole($decodedToken, 1);

    $uuid = $_GET['uuid'];

    $response = $imageService->deleteImage($uuid);

    http_response_code($response['status']);
    echo json_encode(['message' => $response['message']]);
    exit;
}

// Ako ruta nije pronađena
http_response_code(404);
echo json_encode(['message' => 'Not Found']);
?>
