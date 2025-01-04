<?php
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
include_once __DIR__ . '/../utils/JwtUtils.php';

class AuthMiddleware {
    public function requireAuth() {
        $headers = apache_request_headers();
    
        if (empty($headers['Authorization'])) {
            http_response_code(401);
            echo json_encode(['message' => 'Unauthorized. Token is missing.']);
            exit;
        }
    
        $token = str_replace('Bearer ', '', $headers['Authorization']);
        $decoded = JwtUtils::validateToken($token);
    
        if (!$decoded) {
            http_response_code(401);
            echo json_encode(['message' => 'Unauthorized. Invalid token.']);
            exit;
        }
    
        // Check token validity in the database
        $userModel = new User();
        if (!$userModel->isTokenValid($token)) {
            http_response_code(401);
            echo json_encode(['message' => 'Unauthorized. Token is invalid or expired.']);
            exit;
        }
    
        return $decoded;
    }
    
}
?>
