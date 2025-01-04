<?php
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
include_once __DIR__ . '/../utils/JwtUtils.php';

class AuthMiddleware {
    public function requireAuth() {
        $headers = apache_request_headers();

        // Log authorization header
        error_log("Authorization header: " . json_encode($headers['Authorization'] ?? 'Missing'));

        if (empty($headers['Authorization'])) {
            error_log("Error: Missing Authorization header.");
            http_response_code(401);
            echo json_encode(['message' => 'Unauthorized. Token is missing.']);
            exit;
        }

        $token = str_replace('Bearer ', '', $headers['Authorization']);
        error_log("Received token: $token");

        // Validate token
        $decoded = JwtUtils::validateToken($token);
        if (!$decoded) {
            error_log("Error: Invalid token.");
            http_response_code(401);
            echo json_encode(['message' => 'Unauthorized. Invalid token.']);
            exit;
        }

        // Check token in the database
        $userModel = new User();
        if (!$userModel->isTokenValid($token)) {
            error_log("Error: Token invalid or expired in database.");
            http_response_code(401);
            echo json_encode(['message' => 'Unauthorized. Token is invalid or expired.']);
            exit;
        }

        error_log("Token validated successfully. Decoded payload: " . json_encode($decoded));
        return $decoded;
    }
}

?>
