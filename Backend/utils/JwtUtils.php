<?php
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JwtUtils {
    private static $secretKey;
    private static $algorithm = 'HS256';

    // Initialize the secret key from the environment variable
    public static function init() {
        self::$secretKey = $_ENV['JWT_SECRET_KEY'] ?? 'default-secret-key';
    }

    public static function generateToken($data) {
        $issuedAt = time();
        $expiration = $issuedAt + 3600; // Token is valid for 1 hour

        $payload = array_merge($data, [
            'iat' => $issuedAt,
            'exp' => $expiration
        ]);

        return JWT::encode($payload, self::$secretKey, self::$algorithm);
    }

    public static function validateToken($token) {
        try {
            return JWT::decode($token, new Key(self::$secretKey, self::$algorithm));
        } catch (Exception $e) {
            // Log the error for debugging purposes
            error_log("JWT Validation Error: " . $e->getMessage());
            return null;
        }
    }
}

// Initialize the secret key
JwtUtils::init();
?>
