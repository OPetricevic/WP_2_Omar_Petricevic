<?php
include_once __DIR__ . '/../config/db.php';

function migrateRolePermissionsTable() {
    global $conn;
    $sql = "
        CREATE TABLE IF NOT EXISTS role_permissions (
            role_id INT PRIMARY KEY,          -- Jedinstveni ID za rolu
            role_name VARCHAR(255) NOT NULL, -- Naziv role (npr. 'user', 'creator', 'admin')
            inherited_ids VARCHAR(255) DEFAULT NULL -- Nasleđene role (npr. '1,2')
        );
    ";

    $conn->exec($sql);

    // Ubacivanje osnovnih rola
    $roles = [
        [1, 'user', NULL],
        [2, 'creator', '1'],       // Creator nasleđuje prava user-a
        [3, 'admin', '1,2']        // Admin nasleđuje prava user-a i creator-a
    ];

    $stmt = $conn->prepare("INSERT IGNORE INTO role_permissions (role_id, role_name, inherited_ids) VALUES (?, ?, ?)");
    foreach ($roles as $role) {
        $stmt->execute($role);
    }

    echo "Migracija: Tabela `role_permissions` je kreirana i popunjena osnovnim podacima.\n";
}
?>
