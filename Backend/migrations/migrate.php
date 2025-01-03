<?php
include_once __DIR__ . '/../config/db.php';
include_once __DIR__ . '/../utils/helpers.php';

// Funkcija za kreiranje baze podataka
function createDatabase($dbName) {
    global $conn;
    try {
        // Kreiraj bazu ako ne postoji
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
    $conn->exec("INSERT INTO migrations (name) VALUES ('$migrationName')");
}

// Provjera da li je migracija već izvršena
function isMigrationExecuted($migrationName) {
    global $conn;
    $stmt = $conn->prepare("SELECT COUNT(*) FROM migrations WHERE name = ?");
    $stmt->execute([$migrationName]);
    return $stmt->fetchColumn() > 0;
}

// Kreiranje tabele za praćenje migracija
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

// Funkcija za rollback određene migracije
function rollbackMigration($migrationName) {
    global $conn;

    // Rollback za tabelu `users` (dodaj za druge tabele po potrebi)
    if ($migrationName === 'create_users_table') {
        echo "Rollback: Brisanje tabele `users`...\n";
        $conn->exec("DROP TABLE IF EXISTS users");
    }

    // Brisanje migracije iz praćenja
    $conn->exec("DELETE FROM migrations WHERE name = '$migrationName'");
    echo "Migracija `$migrationName` je obrisana iz praćenja.\n";
}

// Kreiraj bazu i poveži se
$databaseName = "wp_project";
createDatabase($databaseName);

// Poveži se na kreiranu bazu
$conn->exec("USE $databaseName");

// Kreiraj tabelu za praćenje migracija
createMigrationsTable();

// Uvezi migracije
include_once __DIR__ . '/create_users_table.php';
// Dodaj nove migracije ovdje, npr. include_once __DIR__ . '/2025_01_01_create_news_table.php';

// Pokretanje svih migracija
function runMigrations() {
    echo "Pokretanje migracija...\n";

    // Migracija: `create_users_table`
    if (!isMigrationExecuted('create_users_table')) {
        migrate();
        trackMigration('create_users_table');
        echo "Migracija `create_users_table` je izvršena.\n";
    } else {
        echo "Migracija `create_users_table` je već izvršena.\n";
    }

    echo "Sve migracije su uspješno završene.\n";
}

// Rollback svih migracija (opcionalno)
function rollbackAllMigrations() {
    echo "Pokretanje rollback-a za sve migracije...\n";

    // Rollback za `create_users_table`
    if (isMigrationExecuted('create_users_table')) {
        rollbackMigration('create_users_table');
    }

    echo "Rollback svih migracija je završen.\n";
}

// Pokreni migracije
runMigrations();

// Opcionalno: Pokreni rollback (ukloni komentar ako želiš rollback testirati)
// rollbackAllMigrations();
?>
