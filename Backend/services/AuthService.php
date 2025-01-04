<?php
include_once __DIR__ . '/../models/User.php';
include_once __DIR__ . '/../utils/helpers.php';
include_once __DIR__ . '/../utils/JwtUtils.php';


class AuthService {
    public function register($data) {
        try {
            // Validate input
            if (empty($data['first_name']) || empty($data['last_name']) || empty($data['email']) || empty($data['password']) || empty($data['date_of_birth'])) {
                error_log("Validation failed: Missing required fields.");
                return ['status' => 400, 'message' => 'All fields are required.'];
            }
    
            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                error_log("Validation failed: Invalid email format - " . $data['email']);
                return ['status' => 400, 'message' => 'Invalid email format.'];
            }
    
            // Check if the user exists
            $userModel = new User();
            if ($userModel->existsByEmail($data['email'])) {
                error_log("Conflict: User with email {$data['email']} already exists.");
                return ['status' => 409, 'message' => 'User with this email already exists.'];
            }
    
            // Generate a UUID for the user
            $userUuid = generateUuid();
            error_log("Generated UUID for user: $userUuid");
    
            // Create the user
            $userModel->createUser([
                'uuid' => $userUuid,
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'username' => $data['username'],
                'email' => $data['email'],
                'date_of_birth' => $data['date_of_birth'],
                'role' => 1, // Default role = user
                'created_at' => date('Y-m-d H:i:s')
            ]);
    
            // Hash the password
            $hashedPassword = password_hash($data['password'], PASSWORD_BCRYPT);
    
            // Store the hashed password
            $userModel->storePasswordHash([
                'uuid' => generateUuid(),
                'user_uuid' => $userUuid,
                'password_hash' => $hashedPassword,
                'created_at' => date('Y-m-d H:i:s')
            ]);
    
            // Generate a JWT token
            $jwt = JwtUtils::generateToken([
                'uuid' => $userUuid,
                'role' => 1
            ]);
            error_log("JWT generated for user: $userUuid");
    
            // Store the JWT token in the tokens table
            $userModel->storeToken([
                'uuid' => generateUuid(),
                'user_uuid' => $userUuid,
                'user_email' => $data['email'],
                'value' => $jwt,
                'expires_at' => date('Y-m-d H:i:s', time() + 3600),
                'created_at' => date('Y-m-d H:i:s')
            ]);
    
            return [
                'status' => 201,
                'message' => 'User registered successfully.',
                'user_uuid' => $userUuid,
                'token' => $jwt // Include the token here
            ];
        } catch (Exception $e) {
            error_log("Exception in register(): " . $e->getMessage());
            return ['status' => 500, 'message' => 'Internal server error.'];
        }
    }
    
    

    public function login($data) {
        if (empty($data['email']) || empty($data['password'])) {
            return ['status' => 400, 'message' => 'Email and password are required.'];
        }
    
        $userModel = new User();
        $user = $userModel->getUserByEmail($data['email']);
    
        if (!$user) {
            return ['status' => 404, 'message' => 'User not found.'];
        }
    
        if (!password_verify($data['password'], $user['password_hash'])) {
            return ['status' => 401, 'message' => 'Invalid password.'];
        }
    
        // Check if an active token already exists
        $activeToken = $userModel->getActiveToken($user['uuid']);
    
        if ($activeToken) {
            return [
                'status' => 200,
                'message' => 'Login successful.',
                'token' => $activeToken
            ];
        }
    
        // Generate a new token if none exists
        $jwt = JwtUtils::generateToken([
            'uuid' => $user['uuid'],
            'role' => $user['role']
        ]);
    
        // Store the new token in the tokens table
        $userModel->storeToken([
            'uuid' => generateUuid(),
            'user_uuid' => $user['uuid'],
            'user_email' => $user['email'],
            'value' => $jwt,
            'expires_at' => date('Y-m-d H:i:s', time() + 3600),
            'created_at' => date('Y-m-d H:i:s')
        ]);
    
        return [
            'status' => 200,
            'message' => 'Login successful.',
            'token' => $jwt
        ];
    } 

    public function logout($token) {
        $userModel = new User();
    
        // Check if the token exists and is valid
        if (!$userModel->isTokenValid($token)) {
            return [
                'status' => 401,
                'message' => 'Unauthorized. Token is invalid or already revoked.'
            ];
        }
    
        // Revoke the token
        $userModel->revokeToken($token);
    
        return [
            'status' => 200,
            'message' => 'Logout successful. Token has been revoked.'
        ];
    }
    
}
?>
