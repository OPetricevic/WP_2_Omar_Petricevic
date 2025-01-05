<?php
include_once __DIR__ . '/../config/db.php';
include_once __DIR__ . '/../utils/helpers.php';

function createDatabase($dbName) {
    global $conn;
    try {
        $conn->exec("CREATE DATABASE IF NOT EXISTS `$dbName`");
        echo "Database `$dbName` successfully created.\n";
    } catch (PDOException $e) {
        echo "Error creating database: " . $e->getMessage() . "\n";
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
    echo "Table `migrations` created successfully (if not already exists).\n";
}

function rollbackMigration($migrationName) {
    global $conn;

    $tables = [
        'create_users_table' => 'users',
        'create_news_table' => 'news',
        'create_password_tokens_table' => 'password_tokens',
        'create_password_resets_table' => 'password_resets', // Added password_resets
        'create_role_permissions_table' => 'role_permissions',
        'create_tokens_table' => 'tokens',
        'create_system_images_table' => 'system_images',
    ];

    if (isset($tables[$migrationName])) {
        echo "Rollback: Deleting table `" . $tables[$migrationName] . "`...\n";
        $conn->exec("DROP TABLE IF EXISTS `" . $tables[$migrationName] . "`");
    }

    $stmt = $conn->prepare("DELETE FROM migrations WHERE name = :migrationName");
    $stmt->bindParam(':migrationName', $migrationName);
    $stmt->execute();
    echo "Migration `$migrationName` removed from tracking.\n";
}

// Create and connect to the database
$databaseName = "wp_project";
createDatabase($databaseName);
$conn->exec("USE `$databaseName`");
createMigrationsTable();

// Include migrations
include_once __DIR__ . '/create_users_table.php';
include_once __DIR__ . '/create_news_table.php';
include_once __DIR__ . '/create_password_tokens_table.php';
include_once __DIR__ . '/create_password_resets_table.php'; // Added password_resets
include_once __DIR__ . '/create_role_permissions_table.php';
include_once __DIR__ . '/create_tokens_table.php';
include_once __DIR__ . '/create_system_images_table.php';

function runMigrations() {
    echo "Running migrations...\n";

    $migrations = [
        'create_users_table' => 'migrateUsersTable',
        'create_news_table' => 'migrateNewsTable',
        'create_password_tokens_table' => 'migratePasswordTokensTable',
        'create_password_resets_table' => 'migratePasswordResetsTable', // Added
        'create_role_permissions_table' => 'migrateRolePermissionsTable',
        'create_tokens_table' => 'migrateTokensTable',
        'create_system_images_table' => 'migrateSystemImagesTable',
    ];

    foreach ($migrations as $migrationName => $migrationFunction) {
        if (!isMigrationExecuted($migrationName)) {
            $migrationFunction();
            trackMigration($migrationName);
            echo "Migration `$migrationName` executed.\n";
        } else {
            echo "Migration `$migrationName` already executed.\n";
        }
    }

    echo "All migrations executed successfully.\n";
}

function rollbackAllMigrations() {
    echo "Running rollback for all migrations...\n";

    $migrations = [
        'create_system_images_table',
        'create_tokens_table',
        'create_role_permissions_table',
        'create_password_resets_table', // Added
        'create_password_tokens_table',
        'create_news_table',
        'create_users_table',
    ];

    foreach ($migrations as $migrationName) {
        rollbackMigration($migrationName);
    }

    echo "Rollback of all migrations completed.\n";
}

// Command-line check for rollback
if (php_sapi_name() === 'cli' && isset($argv[1]) && $argv[1] === 'rollback') {
    rollbackAllMigrations();
} else {
    runMigrations();
}
?>
