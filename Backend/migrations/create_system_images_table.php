<?php
function migrateSystemImagesTable() {
    global $conn;

    try {
        $conn->exec("
            CREATE TABLE IF NOT EXISTS system_images (
                uuid VARCHAR(255) NOT NULL,
                module_uuid VARCHAR(255) NOT NULL,
                module_for VARCHAR(50) NOT NULL,
                url VARCHAR(255) NOT NULL UNIQUE,
                description LONGTEXT,
                date_created DATETIME(3) NOT NULL DEFAULT CURRENT_TIMESTAMP(3),
                date_updated DATETIME(3) DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP(3),
                PRIMARY KEY (uuid)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
        echo "Migracija za 'system_images' tabelu uspješno izvršena.\n";
    } catch (PDOException $e) {
        echo "Greška prilikom kreiranja 'system_images' tabele: " . $e->getMessage() . "\n";
        exit;
    }
}
?>
