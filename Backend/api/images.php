<?php
include_once __DIR__ . '/../middleware/AuthMiddleware.php';
include_once __DIR__ . '/../middleware/RoleMiddleware.php';
include_once __DIR__ . '/../services/ImageService.php';

// Middleware for authentication
$authMiddleware = new AuthMiddleware();
$decodedToken = $authMiddleware->requireAuth();

// Middleware for role checks
$roleMiddleware = new RoleMiddleware();

// Service for image operations
$imageService = new ImageService();

// Endpoint for creating an image
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_SERVER['REQUEST_URI'] === '/images') {
    error_log("POST /images endpoint hit.");

    // Check minimum role: user
    $roleMiddleware->requireRole($decodedToken, [1]);

    $input = json_decode(file_get_contents('php://input'), true);
    error_log("Request payload: " . json_encode($input));

    // Validate input
    if (empty($input['module_uuid']) || empty($input['url'])) {
        error_log("Validation failed: module_uuid or url is missing.");
        http_response_code(400);
        echo json_encode(['message' => 'module_uuid and url are required.']);
        exit;
    }

    // Delete old image (if exists)
    $imageService->deleteOldImage($input['module_uuid']);

    // Add new image
    $response = $imageService->addImage([
        'module_uuid' => $input['module_uuid'],
        'url' => $input['url'],
        'description' => $input['description'] ?? null
    ]);

    // Set response
    http_response_code($response['status']);
    echo json_encode(['message' => $response['message']]);
    exit;
}

// Endpoint for getting all images by module_uuid
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['module_uuid'])) {
    error_log("GET /images?module_uuid endpoint hit.");

    // Check minimum role: user
    $roleMiddleware->requireRole($decodedToken, 1);

    $moduleUuid = $_GET['module_uuid'];
    $images = $imageService->getAllImages($moduleUuid);

    http_response_code(200);
    echo json_encode(['images' => $images]);
    exit;
}

// Endpoint for getting a single image by uuid
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['uuid'])) {
    error_log("GET /images?uuid endpoint hit.");

    // Check minimum role: user
    $roleMiddleware->requireRole($decodedToken, 1);

    $uuid = $_GET['uuid'];
    $image = $imageService->getImageByUuid($uuid);

    if (!$image) {
        error_log("Image not found for uuid: $uuid.");
        http_response_code(404);
        echo json_encode(['message' => 'Image not found.']);
        exit;
    }

    http_response_code(200);
    echo json_encode(['image' => $image]);
    exit;
}

// Endpoint for updating image description
if ($_SERVER['REQUEST_METHOD'] === 'PATCH' && isset($_GET['uuid'])) {
    error_log("PATCH /images?uuid endpoint hit.");

    // Check minimum role: user
    $roleMiddleware->requireRole($decodedToken, 1);

    $uuid = $_GET['uuid'];
    $input = json_decode(file_get_contents('php://input'), true);
    error_log("Request payload: " . json_encode($input));

    if (empty($input['description'])) {
        error_log("Validation failed: description is missing.");
        http_response_code(400);
        echo json_encode(['message' => 'Description is required.']);
        exit;
    }

    $response = $imageService->updateImageDescription($uuid, $input['description']);

    http_response_code($response['status']);
    echo json_encode(['message' => $response['message']]);
    exit;
}

// Endpoint for deleting an image by uuid
if ($_SERVER['REQUEST_METHOD'] === 'DELETE' && isset($_GET['uuid'])) {
    error_log("DELETE /images?uuid endpoint hit.");

    // Check minimum role: user
    $roleMiddleware->requireRole($decodedToken, [1]);

    $uuid = $_GET['uuid'];
    $response = $imageService->deleteImage($uuid);

    http_response_code($response['status']);
    echo json_encode(['message' => $response['message']]);
    exit;
}

// If route is not found
error_log("Route not found.");
http_response_code(404);
echo json_encode(['message' => 'Not Found']);
?>
