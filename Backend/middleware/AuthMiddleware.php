<?php
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
include_once __DIR__ . '/../services/AuthService.php';

class AuthMiddleware {
    private $authService;

    public function __construct() {
        $this->authService = new AuthService();
    }

    public function requireAuth() {
        $headers = apache_request_headers();

        if (empty($headers['Authorization'])) {
            http_response_code(401);
            echo json_encode(['message' => 'Unauthorized. Token is missing.']);
            exit;
        }

        $token = str_replace('Bearer ', '', $headers['Authorization']);

        $decoded = $this->authService->validateToken($token);

        if (!$decoded) {
            http_response_code(401);
            echo json_encode(['message' => 'Unauthorized. Invalid token.']);
            exit;
        }

        return $decoded; // Token je validan, vraća podatke za dalje korišćenje
    }
}
?>
