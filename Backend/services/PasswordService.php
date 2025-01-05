<?php

require_once __DIR__ . '/../utils/passwordReset.php';
require_once __DIR__ . '/../models/PasswordResetToken.php';
require_once __DIR__ . '/../models/User.php';

class PasswordService {
    private $userModel;
    private $passwordResetModel;

    public function __construct($dbConnection) {
        $this->userModel = new User($dbConnection);
        $this->passwordResetModel = new PasswordResetToken($dbConnection);
    }

    public function requestPasswordReset($email) {
        try {
            // Check if user exists
            $user = $this->userModel->getUserByEmail($email);
            if (!$user) {
                return ['status' => 404, 'message' => 'User not found.'];
            }

            // Generate reset token
            $resetToken = bin2hex(random_bytes(16)); // Secure random token
            $expiresAt = date('Y-m-d H:i:s', time() + 3600); // 1 hour expiration

            // Store token in the database
            $this->passwordResetModel->storePasswordResetToken($user['uuid'], $resetToken, $expiresAt);

            // Send email
            sendPasswordResetEmail($email, $resetToken);

            return ['status' => 200, 'message' => 'Password reset email has been sent to your registered email address.'];
        } catch (Exception $e) {
            error_log("Error in requestPasswordReset(): " . $e->getMessage());
            return ['status' => 500, 'message' => 'Internal server error.'];
        }
    }

    public function resetPassword($token, $newPassword) {
        try {
            // Retrieve the reset token details
            $resetTokenData = $this->passwordResetModel->getPasswordResetToken($token);
            if (!$resetTokenData) {
                return ['status' => 404, 'message' => 'Invalid or expired token.'];
            }

            // Check if the token is expired
            $currentTimestamp = time();
            $expiresAt = strtotime($resetTokenData['expires_at']);
            if ($currentTimestamp > $expiresAt) {
                return ['status' => 400, 'message' => 'Token has expired.'];
            }

            // Hash the new password
            $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);

            // Update the user's password
            $userUuid = $resetTokenData['user_uuid'];
            $this->userModel->updatePassword($userUuid, $hashedPassword);

            // Invalidate the reset token
            $this->passwordResetModel->invalidatePasswordResetToken($token);

            return ['status' => 200, 'message' => 'Password reset successfully.'];
        } catch (Exception $e) {
            error_log("Error in resetPassword(): " . $e->getMessage());
            return ['status' => 500, 'message' => 'Internal server error.'];
        }
    }
}
