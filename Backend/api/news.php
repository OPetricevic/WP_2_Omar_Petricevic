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
// GET: Fetch news by UUID
if ($_SERVER['REQUEST_METHOD'] === 'GET' && preg_match('/^\/news\/([^\/]+)$/', $requestUri, $matches)) {
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


// Endpoint for updating news
if ($_SERVER['REQUEST_METHOD'] === 'PATCH' && isset($_GET['uuid'])) {
    error_log("PATCH /news endpoint hit.");

    // Middleware for authentication
    $authMiddleware = new AuthMiddleware();
    $decodedToken = $authMiddleware->requireAuth();

    // Middleware for role checks
    $roleMiddleware = new RoleMiddleware();
    $roleMiddleware->requireRole($decodedToken, [2]);

    $uuid = $_GET['uuid'];
    $input = json_decode(file_get_contents('php://input'), true);
    error_log("Request payload: " . json_encode($input));

    $allowedCategories = ['Technology', 'Sports', 'Lifestyle', 'Business', 'Entertainment'];
    if (!in_array($input['category'], $allowedCategories)) {
        http_response_code(400);
        echo json_encode(['message' => 'Invalid category.']);
        exit;
    }

    // Validate input
    if (empty($input['title']) || empty($input['body']) || empty($input['category'])) {
        error_log("Validation failed: Missing title, body, or category.");
        http_response_code(400);
        echo json_encode(['message' => 'Title, body, and category are required.']);
        exit;
    }

    // Call the NewsService to update the news
    $newsService = new NewsService();
    $response = $newsService->updateNews($uuid, $input['title'], $input['body'], $input['category']);

    http_response_code($response['status']);
    echo json_encode(['message' => $response['message']]);
    exit;
}



// DELETE: Obriši vijest po UUID-u (samo kreatori)
if ($_SERVER['REQUEST_METHOD'] === 'DELETE' && preg_match('/^\/news\/([^\/]+)$/', $_SERVER['REQUEST_URI'], $matches)) {
    $newsUuid = $matches[1];

    // Middleware za autentifikaciju
    $authMiddleware = new AuthMiddleware();
    $decodedToken = $authMiddleware->requireAuth(); // Validacija JWT

    // Middleware za provjeru role
    $roleMiddleware = new RoleMiddleware();
    $roleMiddleware->requireRole($decodedToken, [2, 3]); // Samo kreatori (2) i admini (3)

    try {
        // Provjera da li UUID postoji
        if (empty($newsUuid)) {
            http_response_code(400);
            echo json_encode(['message' => 'UUID is required.']);
            exit;
        }

        // Kreiramo instancu servisa
        $newsService = new NewsService();
        $response = $newsService->deleteNews($newsUuid, $decodedToken);

        // Vraćamo status
        http_response_code($response['status']);
        echo json_encode($response, JSON_PRETTY_PRINT);
    } catch (Exception $e) {
        error_log("Error while deleting news: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['message' => 'Internal server error.']);
    }

    exit;
}


// Ako ruta nije pronađena
http_response_code(404);
echo json_encode(['message' => 'Endpoint not found']);
?>
