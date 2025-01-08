<?php
include_once __DIR__ . '/../models/User.php';
include_once __DIR__ . '/../utils/helpers.php';
include_once __DIR__ . '/../utils/JwtUtils.php';


class AuthService {
    public function register($data) {
        try {
            error_log("Starting user registration: " . json_encode($data));
    
            // Validate required fields
            if (empty($data['first_name']) || empty($data['last_name']) || empty($data['email']) || empty($data['password']) || empty($data['confirm_password']) || empty($data['date_of_birth'])) {
                error_log("Validation failed: Missing required fields.");
                return ['status' => 400, 'message' => 'All fields are required.'];
            }
    
            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                error_log("Validation failed: Invalid email format - " . $data['email']);
                return ['status' => 400, 'message' => 'Invalid email format.'];
            }
    
            // Check if passwords match
            if (!passwordsMatch($data['password'], $data['confirm_password'])) {
                error_log("Validation failed: Passwords do not match.");
                return ['status' => 400, 'message' => 'Passwords do not match.'];
            }
    
            // Check if the user already exists by email
            $userModel = new User();
            if ($userModel->existsByEmail($data['email'])) {
                error_log("Conflict: User with email {$data['email']} already exists.");
                return ['status' => 409, 'message' => 'A user with this email already exists.'];
            }
    
            // Check if the username already exists
            if ($userModel->existsByUsername($data['username'])) {
                error_log("Conflict: Username {$data['username']} already exists.");
                return ['status' => 409, 'message' => 'This username is already taken.'];
            }
    
            // Generate UUID
            $userUuid = generateUuid();
            error_log("Generated UUID for user: $userUuid");
    
            // Create user
            $userModel->createUser([
                'uuid' => $userUuid,
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'username' => $data['username'] ?? null,
                'email' => $data['email'],
                'date_of_birth' => $data['date_of_birth'],
                'role' => 1, // Default role = user
                'created_at' => date('Y-m-d H:i:s')
            ]);
            error_log("User created successfully: $userUuid");
    
            // Hash password
            $hashedPassword = password_hash($data['password'], PASSWORD_BCRYPT);
            error_log("Password hashed successfully.");
    
            // Store password hash
            $userModel->storePasswordHash([
                'uuid' => generateUuid(),
                'user_uuid' => $userUuid,
                'password_hash' => $hashedPassword,
                'created_at' => date('Y-m-d H:i:s')
            ]);
            error_log("Password hash stored successfully.");
    
            // Generate JWT token
            $jwt = JwtUtils::generateToken([
                'uuid' => $userUuid,
                'role' => 1
            ]);
            error_log("JWT generated successfully.");
    
            // Store JWT token
            $userModel->storeToken([
                'uuid' => generateUuid(),
                'user_uuid' => $userUuid,
                'user_email' => $data['email'],
                'value' => $jwt,
                'expires_at' => date('Y-m-d H:i:s', time() + 36000),
                'created_at' => date('Y-m-d H:i:s')
            ]);
            error_log("JWT token stored successfully.");
    
            return [
                'status' => 201,
                'message' => 'User registered successfully.',
                'user_uuid' => $userUuid,
                'token' => $jwt
            ];
        } catch (PDOException $e) {
            // Handle database errors
            if ($e->getCode() === '23000') { // SQLSTATE code for duplicate entries
                error_log("Database constraint violation: " . $e->getMessage());
    
                if (strpos($e->getMessage(), 'users.email') !== false) {
                    return ['status' => 409, 'message' => 'A user with this email already exists.'];
                }
    
                if (strpos($e->getMessage(), 'users.username') !== false) {
                    return ['status' => 409, 'message' => 'This username is already taken.'];
                }
    
                return ['status' => 409, 'message' => 'Conflict: Duplicate entry.'];
            }
    
            error_log("Exception in register(): " . $e->getMessage());
            return ['status' => 500, 'message' => 'Internal server error.'];
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
            'expires_at' => date('Y-m-d H:i:s', time() + 36000),
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
