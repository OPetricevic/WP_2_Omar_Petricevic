<?php

include_once __DIR__ . '/../config/db.php';

class PasswordResetToken {
    private $conn;

    public function __construct($dbConnection) {
        $this->conn = $dbConnection;
    }

    public function getPasswordResetToken($token) {
        $stmt = $this->conn->prepare("SELECT * FROM password_resets WHERE token = :token");
        $stmt->execute([':token' => $token]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function invalidatePasswordResetToken($token) {
        $stmt = $this->conn->prepare("DELETE FROM password_resets WHERE token = :token");
        $stmt->execute([':token' => $token]);
    }

    public function storePasswordResetToken($userUuid, $token, $expiresAt) {
        $stmt = $this->conn->prepare("
            INSERT INTO password_resets (user_uuid, token, expires_at, created_at)
            VALUES (:user_uuid, :token, :expires_at, NOW())
        ");
        $stmt->execute([
            ':user_uuid' => $userUuid,
            ':token' => $token,
            ':expires_at' => $expiresAt,
        ]);
    }
}
