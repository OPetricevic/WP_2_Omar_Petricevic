<?php
include_once __DIR__ . '/../services/NewsService.php';
include_once __DIR__ . '/../middleware/AuthMiddleware.php';
include_once __DIR__ . '/../middleware/RoleMiddleware.php';

// Inicijalizacija servisa i middleware-a
$newsService = new NewsService();
$authMiddleware = new AuthMiddleware();
$roleMiddleware = new RoleMiddleware();

// GET: Dohvati sve vijesti sa paginacijom i pretragom
// Parse URI path (exclude query parameters)
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// GET: Dohvati sve vijesti sa paginacijom i pretragom
if ($_SERVER['REQUEST_METHOD'] === 'GET' && $requestUri === '/news') {
    try {
        $page = isset($_GET['current_page']) ? (int)$_GET['current_page'] : 1;
        $pageSize = isset($_GET['page_size']) ? (int)$_GET['page_size'] : 10;
        $search = isset($_GET['search']) ? $_GET['search'] : null;

        $response = $newsService->getPaginatedNews($page, $pageSize, $search);
        http_response_code(200);
        echo json_encode($response, JSON_PRETTY_PRINT);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Internal server error.']);
    }
    exit;
}


// GET: Dohvati vijest po UUID-u
if ($_SERVER['REQUEST_METHOD'] === 'GET' && preg_match('/^\/news\/([^\/]+)$/', $_SERVER['REQUEST_URI'], $matches)) {
    $newsUuid = $matches[1];
    try {
        $news = $newsService->getNewsByUuid($newsUuid);
        if (!$news) {
            http_response_code(404);
            echo json_encode(['message' => 'News not found']);
            exit;
        }
        http_response_code(200);
        echo json_encode($news, JSON_PRETTY_PRINT);
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

// POST: Kreiraj novu vijest (samo kreatori)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_SERVER['REQUEST_URI'] === '/news') {
    $decodedToken = $authMiddleware->requireAuth(); // Validate JWT
    $roleMiddleware->requireRole($decodedToken, [2]); // Allow only creators (role 2)

    $data = json_decode(file_get_contents('php://input'), true);

    // Validate required fields
    if (empty($data['title']) || empty($data['body']) || empty($data['category'])) {
        http_response_code(400);
        echo json_encode(['message' => 'Missing required fields: title, body, or category']);
        exit;
    }

    try {
        $response = $newsService->createNews(
            $decodedToken->uuid, 
            $data['title'], 
            $data['body'], 
            $data['category']
        );
        http_response_code(201);
        echo json_encode($response, JSON_PRETTY_PRINT);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['message' => 'Internal server error.', 'error' => $e->getMessage()], JSON_PRETTY_PRINT);
    }
    exit;
}


// DELETE: Obriši vijest po UUID-u (samo kreatori)
if ($_SERVER['REQUEST_METHOD'] === 'DELETE' && preg_match('/^\/news\/([^\/]+)$/', $_SERVER['REQUEST_URI'], $matches)) {
    $newsUuid = $matches[1];
    $decodedToken = $authMiddleware->requireAuth(); // Validacija JWT
    $roleMiddleware->requireRole($decodedToken, [2]); // Samo kreatori (role 2)

    try {
        $response = $newsService->deleteNews($newsUuid, $decodedToken->uuid);
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

// Ako ruta nije pronađena
http_response_code(404);
echo json_encode(['message' => 'Endpoint not found']);
?>
