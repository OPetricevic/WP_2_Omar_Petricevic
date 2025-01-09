<?php
include_once __DIR__ . '/../services/AuthService.php';
include_once __DIR__ . '/../services/PasswordService.php';
include_once __DIR__ . '/../config/db.php';

// Inicijalizacija AuthService
$authService = new AuthService();
$passwordService = new PasswordService($conn); 

// Endpoint za registraciju
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_SERVER['REQUEST_URI'] === '/auth/register') {
    $input = json_decode(file_get_contents('php://input'), true);

    // Check if password and confirm_password match
    if (empty($input['password']) || empty($input['confirm_password']) || !passwordsMatch($input['password'], $input['confirm_password'])) {
        http_response_code(400);
        echo json_encode(['message' => 'Passwords do not match.']);
        exit;
    }

    $response = $authService->register($input);

    // Log debug info
    if ($response['status'] >= 400) {
        error_log("Register failed: " . json_encode($response));
    }

    // Send the response
    http_response_code($response['status']);
    header('Content-Type: application/json');

    // Add the token to the response if the registration is successful
    if ($response['status'] === 201) {
        $response['token'] = $response['token'] ?? null;
    }

    echo json_encode($response);
    exit;
}

// Endpoint za login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_SERVER['REQUEST_URI'] === '/auth/login') {
    // Učitaj podatke iz body-a
    $input = json_decode(file_get_contents('php://input'), true);

    $response = $authService->login($input);

    // Postavi HTTP status
    http_response_code($response['status']);
    header('Content-Type: application/json');

    // Dodaj status i token u JSON odgovor
    echo json_encode([
        'status' => $response['status'],
        'message' => $response['message'],
        'token' => $response['token'] ?? null
    ]);
    exit;
}

// Endpoint for logout
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_SERVER['REQUEST_URI'] === '/auth/logout') {
    // Extract the token from the Authorization header
    $headers = apache_request_headers();
    if (empty($headers['Authorization'])) {
        http_response_code(401);
        echo json_encode(['message' => 'Unauthorized. Token is missing.']);
        exit;
    }

    $token = str_replace('Bearer ', '', $headers['Authorization']);

    // Use the AuthService to revoke the token
    $authService = new AuthService();
    $response = $authService->logout($token);

    http_response_code($response['status']);
    echo json_encode(['message' => $response['message']]);
    exit;
}

// Password Request: Send reset email
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_SERVER['REQUEST_URI'] === '/auth/request-password-reset') {
    $email = json_decode(file_get_contents('php://input'), true)['email'];

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(['message' => 'Invalid email address.']);
        exit;
    }

    $response = $passwordService->requestPasswordReset($email);
    http_response_code($response['status']);
    echo json_encode(['message' => $response['message']]);
    exit;
}


// Reset Password: Update password using token
if ($_SERVER['REQUEST_METHOD'] === 'POST' && parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) === '/auth/reset-password') {
    $data = json_decode(file_get_contents('php://input'), true);
    $token = $data['token'];
    $newPassword = $data['password'];
    $confirmPassword = $data['confirm_password'];

    if ($newPassword !== $confirmPassword) {
        http_response_code(400);
        echo json_encode(['message' => 'Passwords do not match.']);
        exit;
    }

    $response = $passwordService->resetPassword($token, $newPassword);
    http_response_code($response['status']);
    echo json_encode(['message' => $response['message']]);
    exit;
}


// Ako ruta nije pronađena
http_response_code(404);
echo json_encode(['message' => 'Not Found']);
?>
