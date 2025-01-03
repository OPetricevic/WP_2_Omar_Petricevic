<?php
include_once '../config/db.php';

function migrate() {
    global $conn;
    $sql = "
        CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            email VARCHAR(100) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );
    ";
    $conn->exec($sql);
    echo "Migracija: Tabela `users` je kreirana.\n";
}
?>
