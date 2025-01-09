<?php
include_once __DIR__ . '/../middleware/AuthMiddleware.php';
include_once __DIR__ . '/../middleware/RoleMiddleware.php';
include_once __DIR__ . '/../services/ImageService.php';
include_once __DIR__ . '/../models/User.php';

// Middleware for authentication
$authMiddleware = new AuthMiddleware();
$decodedToken = $authMiddleware->requireAuth();

// Middleware for role checks
$roleMiddleware = new RoleMiddleware();

// Service for image operations
$imageService = new ImageService();

// Endpoint for uploading an image
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_SERVER['REQUEST_URI'] === '/images') {
    error_log("POST /images endpoint hit.");

    // Check minimum role: user
    $roleMiddleware->requireRole($decodedToken, [1]);

    // Check if a file is uploaded
    $uploadedFile = $_FILES['image'] ?? null;
    if (!$uploadedFile || $uploadedFile['error'] !== UPLOAD_ERR_OK) {
        error_log("File upload failed or no file provided.");
        http_response_code(400);
        echo json_encode(['message' => 'Image file is required and must be valid.']);
        exit;
    }

    // Validate other input fields
    $moduleUuid = $_POST['module_uuid'] ?? null;
    $moduleFor = $_POST['module_for'] ?? null;
    $description = $_POST['description'] ?? null;

    if (!$moduleUuid) {
        error_log("Validation failed: module_uuid is missing.");
        http_response_code(400);
        echo json_encode(['message' => 'module_uuid is required.']);
        exit;
    }

    if (!$moduleFor) {
        error_log("Validation failed: module_for is missing.");
        http_response_code(400);
        echo json_encode(['message' => 'module_for is required.']);
        exit;
    }

    // Add the image using the ImageService
    $response = $imageService->addImage(
        $uploadedFile,
        $moduleUuid,
        $moduleFor,
        $description
    );

    // Check response and set the appropriate status
    http_response_code($response['status']);
    echo json_encode([
        'message' => $response['message'],
        'url' => $response['file_url'] ?? null // Return the uploaded file URL if available
    ]);
    exit;
}

// Endpoint for getting all images by module_uuid and module_for
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['module_uuid']) && isset($_GET['module_for'])) {
    error_log("GET /images?module_uuid&module_for endpoint hit.");

    // Check minimum role: user
    $roleMiddleware->requireRole($decodedToken, [1]);

    $moduleUuid = $_GET['module_uuid'];
    $moduleFor = $_GET['module_for'];

    // Fetch images using the ImageService
    $images = $imageService->getAllImages($moduleUuid, $moduleFor);

    http_response_code(200);
    echo json_encode(['images' => $images]);
    exit;
}


// Endpoint for getting a single image by uuid
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['uuid'])) {
    error_log("GET /images?uuid endpoint hit.");

    // Check minimum role: user
    $roleMiddleware->requireRole($decodedToken, [1]);

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
    $roleMiddleware->requireRole($decodedToken, [1]);

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

if ($_SERVER['REQUEST_METHOD'] === 'GET' && preg_match('/^\/images\/module\/([^\/]+)$/', $requestUri, $matches)) {
    $moduleUuid = $matches[1];
    try {
        $image = $imageService->getImageByModuleUuid($moduleUuid);
        if (!$image) {
            http_response_code(404);
            echo json_encode(['message' => 'Image not found']);
            exit;
        }

        http_response_code(200);
        echo json_encode($image, JSON_PRETTY_PRINT);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'message' => 'Internal server error.',
            'error' => $e->getMessage()
        ], JSON_PRETTY_PRINT);
    }
    exit;
}


// If route is not found
error_log("Route not found.");
http_response_code(404);
echo json_encode(['message' => 'Not Found']);
?>
