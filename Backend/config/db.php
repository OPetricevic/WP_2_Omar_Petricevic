<?php
include_once __DIR__ . '/../utils/helpers.php';

// Prikaz grešaka za debug (samo u razvoju, isključiti u produkciji)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

try {
    // Učitaj `.env` fajl
    loadEnv(__DIR__ . '/../.env');

    // Proveri da li su potrebne promenljive iz `.env` postavljene
    $requiredEnv = ['DB_HOST', 'DB_PORT', 'DB_NAME', 'DB_USER', 'DB_PASS'];
    foreach ($requiredEnv as $envVar) {
        if (empty($_ENV[$envVar])) {
            throw new Exception("Missing required environment variable: $envVar");
        }
    }

    // Preuzmi varijable iz `.env`
    $host = $_ENV['DB_HOST'];
    $port = $_ENV['DB_PORT'];
    $dbname = $_ENV['DB_NAME'];
    $user = $_ENV['DB_USER'];
    $password = $_ENV['DB_PASS'];

    // Povezivanje na MySQL server
    $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
    $conn = new PDO($dsn, $user, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Logovanje uspešne konekcije (opcionalno za debug)
    error_log("Connected to the database successfully.");
} catch (PDOException $e) {
    // Loguj grešku i prikaži prijateljsku poruku korisniku
    error_log("Database Connection Failed: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(["error" => "Database connection failed. Please try again later."]);
    exit;
} catch (Exception $e) {
    // Loguj greške vezane za environment
    error_log("Configuration Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(["error" => "Configuration error. Please contact support."]);
    exit;
}
?>
