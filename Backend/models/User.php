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
            error_log("Attempting to insert user: " . json_encode($userData));
            $stmt = $this->conn->prepare("
                INSERT INTO users (uuid, first_name, last_name, username, email, date_of_birth, role, created_at)
                VALUES (:uuid, :first_name, :last_name, :username, :email, :date_of_birth, :role, :created_at)
            ");
            $stmt->execute($userData);
            error_log("User successfully inserted.");
        } catch (PDOException $e) {
            error_log("Error in createUser(): " . $e->getMessage());
            throw $e;
        }
    }
   
    
    public function storePasswordHash($tokenData) {
        try {
            $stmt = $this->conn->prepare("
                INSERT INTO password_tokens (uuid, user_uuid, password_hash, created_at)
                VALUES (:uuid, :user_uuid, :password_hash, :created_at)
            ");
            $stmt->execute($tokenData);
            error_log("Password hash stored: " . json_encode($tokenData));
        } catch (PDOException $e) {
            error_log("Database error in storePasswordHash(): " . $e->getMessage());
            throw $e;
        }
    }
    
    public function storeToken($tokenData) {
        try {
            $stmt = $this->conn->prepare("
                INSERT INTO tokens (uuid, user_uuid, user_email, value, expires_at, created_at)
                VALUES (:uuid, :user_uuid, :user_email, :value, :expires_at, :created_at)
            ");
            $stmt->execute($tokenData);
            error_log("JWT token stored: " . json_encode($tokenData));
        } catch (PDOException $e) {
            error_log("Database error in storeToken(): " . $e->getMessage());
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

    public function getUserByEmail($email) {
        try {
            $stmt = $this->conn->prepare("
                SELECT users.*, pt.password_hash
                FROM users
                JOIN password_tokens pt ON users.uuid = pt.user_uuid
                WHERE users.email = :email
            ");
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error in getUserByEmail(): " . $e->getMessage());
            return null;
        }
    }

    public function getActiveToken($userUuid) {
        try {
            $stmt = $this->conn->prepare("
                SELECT value FROM tokens
                WHERE user_uuid = :user_uuid AND expires_at > NOW() AND revoked_at IS NULL
                ORDER BY expires_at DESC LIMIT 1
            ");
            $stmt->bindParam(':user_uuid', $userUuid);
            $stmt->execute();
            return $stmt->fetchColumn(); // Return the token value if it exists
        } catch (PDOException $e) {
            error_log("Error in getActiveToken(): " . $e->getMessage());
            return null;
        }
    }  
    
    public function isTokenValid($token) {
        try {
            $stmt = $this->conn->prepare("
                SELECT COUNT(*) FROM tokens
                WHERE value = :token AND expires_at > NOW() AND revoked_at IS NULL
            ");
            $stmt->bindParam(':token', $token);
            $stmt->execute();
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log("Error in isTokenValid(): " . $e->getMessage());
            return false;
        }
    }
    
    public function revokeToken($token) {
        try {
            $stmt = $this->conn->prepare("
                UPDATE tokens
                SET revoked_at = NOW()
                WHERE value = :token
            ");
            $stmt->bindParam(':token', $token);
            $stmt->execute();
            error_log("Token revoked: $token");
        } catch (PDOException $e) {
            error_log("Error in revokeToken(): " . $e->getMessage());
            throw $e;
        }
    }
    
    public function existsByUsername($username) {
        try {
            $stmt = $this->conn->prepare("SELECT COUNT(*) FROM users WHERE username = :username");
            $stmt->bindParam(':username', $username);
            $stmt->execute();
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log("Error in existsByUsername(): " . $e->getMessage());
            return false;
        }
    }
    
}
?>
