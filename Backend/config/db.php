<?php
include_once __DIR__ . '/../utils/helpers.php';

try {
    // Učitaj `.env` fajl
    loadEnv(__DIR__ . '/../.env');

    // Preuzmi varijable iz `.env`
    $host = $_ENV['DB_HOST'];
    $port = $_ENV['DB_PORT'];
    $dbname = $_ENV['DB_NAME'];
    $user = $_ENV['DB_USER'];
    $password = $_ENV['DB_PASS'];

    // Povezivanje na MySQL server
    $conn = new PDO("mysql:host=$host;port=$port", $user, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Konekcija nije uspjela: " . $e->getMessage());
}
?>
