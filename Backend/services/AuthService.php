<?php
include_once __DIR__ . '/../models/User.php';
include_once __DIR__ . '/../utils/helpers.php';

class AuthService {
    public function register($data) {
        try {
            // Validacija podataka
            if (empty($data['first_name']) || empty($data['last_name']) || empty($data['email']) || empty($data['password']) || empty($data['date_of_birth'])) {
                return ['status' => 400, 'message' => 'All fields are required.'];
            }

            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                return ['status' => 400, 'message' => 'Invalid email format.'];
            }

            // Provjera da li korisnik već postoji
            $userModel = new User();
            if ($userModel->existsByEmail($data['email'])) {
                return ['status' => 409, 'message' => 'User with this email already exists.'];
            }

            // Generisanje UUID-a za korisnika
            $userUuid = generateUuid();

            // Kreiranje korisnika
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

            // Hashiranje lozinke
            $hashedPassword = password_hash($data['password'], PASSWORD_BCRYPT);

            // Pohrana hashirane lozinke u tabelu `user_tokens`
            $userModel->storePasswordHash([
                'uuid' => generateUuid(),
                'user_uuid' => $userUuid,
                'password_hash' => $hashedPassword,
                'created_at' => date('Y-m-d H:i:s')
            ]);

            return ['status' => 201, 'message' => 'User registered successfully.', 'user_uuid' => $userUuid];
        } catch (Exception $e) {
            error_log("Exception in register(): " . $e->getMessage());
            return ['status' => 500, 'message' => 'Internal server error.'];
        }
    }
}
?>
