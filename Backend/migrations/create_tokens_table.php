<?php
include_once __DIR__ . '/../config/db.php';

function migrateTokensTable() {
    global $conn;

    $sql = "
        CREATE TABLE IF NOT EXISTS tokens (
            uuid VARCHAR(255) PRIMARY KEY,
            user_uuid VARCHAR(255) NOT NULL,
            user_email VARCHAR(255) NOT NULL,
            value TEXT NOT NULL,
            expires_at DATETIME(3) NOT NULL,
            created_at DATETIME(3) NOT NULL,
            revoked_at DATETIME(3) DEFAULT NULL,
            FOREIGN KEY (user_uuid) REFERENCES users(uuid) ON DELETE CASCADE
        );
    ";

    $conn->exec($sql);
    echo "Migracija: Tabela `tokens` je kreirana.\n";
}
?>
