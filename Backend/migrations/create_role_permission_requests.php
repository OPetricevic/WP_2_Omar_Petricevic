<?php
include_once __DIR__ . '/../config/db.php';

function migrateRolePermissionRequestsTable() {
    global $conn;

    $sql = "
        CREATE TABLE IF NOT EXISTS role_permission_requests (
            uuid VARCHAR(36) PRIMARY KEY,
            user_uuid VARCHAR(36) NOT NULL,
            requested_role INT NOT NULL,
            status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_uuid) REFERENCES users(uuid) ON DELETE CASCADE
        );
    ";

    $conn->exec($sql);
    echo "Migracija: Tabela `role_permission_requests` je kreirana.\n";
}
?>
