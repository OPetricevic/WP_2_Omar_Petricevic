<?php
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
require_once __DIR__ . '/../../vendor/autoload.php';

class JwtUtils {
    private static $secretKey;
    private static $algorithm = 'HS256';

    /**
     * Initializes the secret key from the .env file.
     * Throws an exception if the key is not set.
     */
    public static function init() {
        $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
        $dotenv->load();

        self::$secretKey = $_ENV['JWT_SECRET_KEY'] ?? null;
        if (empty(self::$secretKey)) {
            throw new Exception("JWT_SECRET_KEY is not set in the .env file.");
        }
    }

    /**
     * Generates a JWT token with the provided data.
     *
     * @param array $data Payload data for the JWT.
     * @return string Encoded JWT token.
     */
    public static function generateToken(array $data): string {
        $issuedAt = time();
        $expiration = $issuedAt + 36000; // Token is valid for 10 hours

        $payload = array_merge($data, [
            'iat' => $issuedAt,
            'exp' => $expiration,
        ]);

        return JWT::encode($payload, self::$secretKey, self::$algorithm);
    }

    /**
     * Validates a given JWT token.
     *
     * @param string $token The JWT token to validate.
     * @return object|null Decoded token data or null if validation fails.
     */
    public static function validateToken(string $token): ?object {
        try {
            $decoded = JWT::decode($token, new Key(self::$secretKey, self::$algorithm));
            return $decoded;
        } catch (Exception $e) {
            error_log("JWT Validation Error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Generates a new JWT token for a user.
     *
     * @param string $userUuid User UUID.
     * @param int $role User role.
     * @return string New JWT token.
     */
    public static function generateJwtToken(string $userUuid, int $role): string {
        return self::generateToken([
            'uuid' => $userUuid,
            'role' => $role
        ]);
    }
}

// Initialize JwtUtils
JwtUtils::init();
?>
