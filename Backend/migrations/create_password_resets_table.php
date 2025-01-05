<?php

function migratePasswordResetsTable() {
    global $conn;

    try {
        $conn->exec("
            CREATE TABLE IF NOT EXISTS password_resets (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_uuid VARCHAR(255) NOT NULL, -- Match this type with `users.uuid`
                token VARCHAR(255) NOT NULL UNIQUE,
                expires_at DATETIME NOT NULL,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_uuid) REFERENCES users(uuid) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
        ");
        echo "Migration for 'password_resets' table executed successfully.\n";
    } catch (PDOException $e) {
        echo "Error creating 'password_resets' table: " . $e->getMessage() . "\n";
        exit;
    }
}


