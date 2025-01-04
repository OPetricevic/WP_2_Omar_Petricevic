<?php
include_once __DIR__ . '/../config/db.php';

function migratePasswordTokensTable() {
    global $conn;

    $sql = "
        CREATE TABLE IF NOT EXISTS password_tokens (
            uuid VARCHAR(255) PRIMARY KEY,
            user_uuid VARCHAR(255) NOT NULL,
            password_hash VARCHAR(255) NOT NULL,
            created_at DATETIME(3) NOT NULL,
            FOREIGN KEY (user_uuid) REFERENCES users(uuid) ON DELETE CASCADE
        );
    ";

    $conn->exec($sql);
    echo "Migracija: Tabela `password_tokens` je kreirana.\n";
}
?>
