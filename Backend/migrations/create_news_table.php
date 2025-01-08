<?php
include_once __DIR__ . '/../config/db.php';

function migrateNewsTable() {
    global $conn;
    $sql = "
        CREATE TABLE IF NOT EXISTS news (
            uuid VARCHAR(255) PRIMARY KEY, -- UUID za vijest
            title VARCHAR(255) NOT NULL,
            body TEXT NOT NULL,
            category ENUM('Technology', 'Sports', 'Lifestyle', 'Business', 'Entertainment') NOT NULL,
            created_at DATETIME(3) NOT NULL,
            author_uuid VARCHAR(255), -- FK prema `users.uuid`, dozvoljava NULL
            FOREIGN KEY (author_uuid) REFERENCES users(uuid) ON DELETE SET NULL
        );
    ";
    $conn->exec($sql);
    echo "Migracija: Tabela `news` je kreirana.\n";
}

?>
