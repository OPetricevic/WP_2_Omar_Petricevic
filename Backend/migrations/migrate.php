<?php
include_once __DIR__ . '/../config/db.php';
include_once __DIR__ . '/../utils/helpers.php';

function createDatabase($dbName) {
    global $conn;
    try {
        $conn->exec("CREATE DATABASE IF NOT EXISTS $dbName");
        echo "Baza `$dbName` je uspešno kreirana.\n";
    } catch (PDOException $e) {
        echo "Greška pri kreiranju baze: " . $e->getMessage() . "\n";
        exit;
    }
}

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

    $tables = [
        'create_users_table' => 'users',
        'create_news_table' => 'news',
        'create_password_tokens_table' => 'password_tokens', // Renamed for clarity
        'create_role_permissions_table' => 'role_permissions',
        'create_tokens_table' => 'tokens', // Added the new tokens table
    ];

    if (isset($tables[$migrationName])) {
        echo "Rollback: Brisanje tabele `" . $tables[$migrationName] . "`...\n";
        $conn->exec("DROP TABLE IF EXISTS " . $tables[$migrationName]);
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
include_once __DIR__ . '/create_password_tokens_table.php'; // Renamed for clarity
include_once __DIR__ . '/create_role_permissions.php';
include_once __DIR__ . '/create_tokens_table.php'; // Added for JWT tokens

function runMigrations() {
    echo "Pokretanje migracija...\n";

    $migrations = [
        'create_users_table' => 'migrateUsersTable',
        'create_news_table' => 'migrateNewsTable',
        'create_password_tokens_table' => 'migratePasswordTokensTable', // Renamed
        'create_role_permissions_table' => 'migrateRolePermissionsTable',
        'create_tokens_table' => 'migrateTokensTable', // Added
    ];

    foreach ($migrations as $migrationName => $migrationFunction) {
        if (!isMigrationExecuted($migrationName)) {
            $migrationFunction();
            trackMigration($migrationName);
            echo "Migracija `$migrationName` je izvršena.\n";
        } else {
            echo "Migracija `$migrationName` je već izvršena.\n";
        }
    }

    echo "Sve migracije su uspešno završene.\n";
}

function rollbackAllMigrations() {
    echo "Pokretanje rollback-a za sve migracije...\n";

    $migrations = [
        'create_tokens_table',
        'create_role_permissions_table',
        'create_password_tokens_table',
        'create_news_table',
        'create_users_table',
    ];

    foreach ($migrations as $migrationName) {
        rollbackMigration($migrationName);
    }

    echo "Rollback svih migracija je završen.\n";
}

// Provera komandne linije za rollback
if (php_sapi_name() === 'cli' && isset($argv[1]) && $argv[1] === 'rollback') {
    rollbackAllMigrations();
} else {
    runMigrations();
}
