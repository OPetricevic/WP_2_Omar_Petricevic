<?php
include_once __DIR__ . '/../config/db.php';

function migrateUsersTable() {
    global $conn;
    $sql = "
        CREATE TABLE IF NOT EXISTS users (
            uuid VARCHAR(255) PRIMARY KEY, -- UUID za korisnika
            first_name VARCHAR(255) NOT NULL,
            last_name VARCHAR(255) NOT NULL,
            username VARCHAR(255) NOT NULL UNIQUE,
            email VARCHAR(255) NOT NULL UNIQUE,
            date_of_birth DATE NOT NULL,
            role INT NOT NULL DEFAULT 1, -- 1 = user
            created_at DATETIME(3) NOT NULL
        );
    ";
    $conn->exec($sql);
    echo "Migracija: Tabela `users` je ažurirana s kolonom `role`.\n";
}
?>
