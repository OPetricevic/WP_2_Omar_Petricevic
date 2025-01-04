<?php
include_once __DIR__ . '/../config/db.php';
include_once __DIR__ . '/../utils/helpers.php';

// Funkcija za kreiranje baze podataka
function createDatabase($dbName) {
    global $conn;
    try {
        $conn->exec("CREATE DATABASE IF NOT EXISTS $dbName");
        echo "Baza `$dbName` je uspješno kreirana.\n";
    } catch (PDOException $e) {
        echo "Greška pri kreiranju baze: " . $e->getMessage() . "\n";
        exit;
    }
}

// Funkcija za praćenje migracija
function trackMigration($migrationName) {
    global $conn;
    $stmt = $conn->prepare("INSERT INTO migrations (name) VALUES (:migrationName)");
    $stmt->bindParam(':migrationName', $migrationName);
    $stmt->execute();
}

function isMigrationExecuted($migrationName) {
    global $conn;
    $stmt = $conn->prepare("SELECT COUNT(*) FROM migrations WHERE name = :migrationName");
    $stmt->bindParam(':migrationName', $migrationName);
    $stmt->execute();
    return $stmt->fetchColumn() > 0;
}

function createMigrationsTable() {
    global $conn;
    $conn->exec("
        CREATE TABLE IF NOT EXISTS migrations (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL UNIQUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );
    ");
    echo "Tabela `migrations` je kreirana (ako već nije postojala).\n";
}

function rollbackMigration($migrationName) {
    global $conn;

    if ($migrationName === 'create_users_table') {
        echo "Rollback: Brisanje tabele `users`...\n";
        $conn->exec("DROP TABLE IF EXISTS users");
    }

    if ($migrationName === 'create_news_table') {
        echo "Rollback: Brisanje tabele `news`...\n";
        $conn->exec("DROP TABLE IF EXISTS news");
    }

    if ($migrationName === 'create_user_tokens_table') {
        echo "Rollback: Brisanje tabele `user_tokens`...\n";
        $conn->exec("DROP TABLE IF EXISTS user_tokens");
    }

    if ($migrationName === 'create_role_permissions_table') {
        echo "Rollback: Brisanje tabele `role_permissions`...\n";
        $conn->exec("DROP TABLE IF EXISTS role_permissions");
    }

    $stmt = $conn->prepare("DELETE FROM migrations WHERE name = :migrationName");
    $stmt->bindParam(':migrationName', $migrationName);
    $stmt->execute();
    echo "Migracija `$migrationName` je obrisana iz praćenja.\n";
}

// Kreiraj bazu i poveži se
$databaseName = "wp_project";
createDatabase($databaseName);
$conn->exec("USE $databaseName");
createMigrationsTable();

// Uvezi migracije
include_once __DIR__ . '/create_users_table.php';
include_once __DIR__ . '/create_news_table.php';
include_once __DIR__ . '/create_user_tokens_table.php';
include_once __DIR__ . '/create_role_permissions.php'; // Dodano

// Pokretanje svih migracija
function runMigrations() {
    echo "Pokretanje migracija...\n";

    if (!isMigrationExecuted('create_users_table')) {
        migrateUsersTable();
        trackMigration('create_users_table');
        echo "Migracija `create_users_table` je izvršena.\n";
    } else {
        echo "Migracija `create_users_table` je već izvršena.\n";
    }

    if (!isMigrationExecuted('create_news_table')) {
        migrateNewsTable();
        trackMigration('create_news_table');
        echo "Migracija `create_news_table` je izvršena.\n";
    } else {
        echo "Migracija `create_news_table` je već izvršena.\n";
    }

    if (!isMigrationExecuted('create_user_tokens_table')) {
        migrateUserTokensTable();
        trackMigration('create_user_tokens_table');
        echo "Migracija `create_user_tokens_table` je izvršena.\n";
    } else {
        echo "Migracija `create_user_tokens_table` je već izvršena.\n";
    }

    if (!isMigrationExecuted('create_role_permissions_table')) {
        migrateRolePermissionsTable(); // Pokreni migraciju za `role_permissions`
        trackMigration('create_role_permissions_table');
        echo "Migracija `create_role_permissions_table` je izvršena.\n";
    } else {
        echo "Migracija `create_role_permissions_table` je već izvršena.\n";
    }

    echo "Sve migracije su uspješno završene.\n";
}

// Pokreni migracije
runMigrations();
?>
