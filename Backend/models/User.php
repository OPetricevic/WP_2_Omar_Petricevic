<?php
include_once __DIR__ . '/../config/db.php';

class User {
    private $conn;

    public function __construct() {
        global $conn;
        $this->conn = $conn;
    }

    public function existsByEmail($email) {
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM users WHERE email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        return $stmt->fetchColumn() > 0;
    }

    public function existsByUuid($uuid) {
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM users WHERE uuid = :uuid");
        $stmt->bindParam(':uuid', $uuid);
        $stmt->execute();
        return $stmt->fetchColumn() > 0;
    }

    public function createUser($userData) {
        try {
            $stmt = $this->conn->prepare("
                INSERT INTO users (uuid, first_name, last_name, username, email, date_of_birth, role, created_at)
                VALUES (:uuid, :first_name, :last_name, :username, :email, :date_of_birth, :role, :created_at)
            ");
            $stmt->execute($userData);
            error_log("User Inserted: " . json_encode($userData));
        } catch (PDOException $e) {
            error_log("Error in createUser(): " . $e->getMessage());
            throw $e;
        }
    }
    
    public function storePasswordHash($tokenData) {
        try {
            $stmt = $this->conn->prepare("
                INSERT INTO user_tokens (uuid, user_uuid, password_hash, created_at)
                VALUES (:uuid, :user_uuid, :password_hash, :created_at)
            ");
            $stmt->execute($tokenData);
            error_log("Password Hash Inserted: " . json_encode($tokenData));
        } catch (PDOException $e) {
            error_log("Error in storePasswordHash(): " . $e->getMessage());
            throw $e;
        }
    }   
    
    public function updateUserRole($uuid, $role) {
        try {
            $stmt = $this->conn->prepare("UPDATE users SET role = :role WHERE uuid = :uuid");
            $stmt->bindParam(':role', $role);
            $stmt->bindParam(':uuid', $uuid);
            $stmt->execute();
            error_log("Role Updated: User UUID $uuid, New Role $role");
        } catch (PDOException $e) {
            error_log("Error in updateUserRole(): " . $e->getMessage());
            throw $e;
        }
    }
}
?>
