<?php

require_once __DIR__ . '/../vendor/autoload.php';

// Display errors for debugging (only in development, disable in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Set default timezone
date_default_timezone_set('Europe/Sarajevo');

// Set CORS headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header('Content-Type: application/json');

// Handle OPTIONS preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Log incoming request
$requestUri = $_SERVER['REQUEST_URI'];
$method = $_SERVER['REQUEST_METHOD'];
error_log("Request: $method $requestUri");

// Parse URI path (exclude query parameters)
$parsedUrl = parse_url($requestUri);
$path = $parsedUrl['path'];

// Route requests
switch (true) {
    case strpos($path, '/auth') === 0:
        include_once __DIR__ . '/api/auth.php';
        break;
    case strpos($path, '/news') === 0:
        include_once __DIR__ . '/api/news.php';
        break;
    case strpos($path, '/users') === 0:
        include_once __DIR__ . '/api/users.php';
        break;
    case strpos($path, '/roles') === 0:
        include_once __DIR__ . '/api/roles.php';
        break;
    case strpos($path, '/images') === 0:
        include_once __DIR__ . '/api/images.php';
        break;
    default:
        routeNotFound($requestUri);
}

// Handle undefined routes
function routeNotFound($requestUri) {
    error_log("404 Not Found: $requestUri");
    http_response_code(404);
    echo json_encode(['message' => 'Endpoint not found']);
    exit;
}
