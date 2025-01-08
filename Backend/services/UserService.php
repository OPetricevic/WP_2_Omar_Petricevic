<?php

include_once __DIR__ . '/../models/User.php';

class UserService {
    private $userModel;

    public function __construct() {
        $this->userModel = new User();
    }

    public function getUserByUuid($uuid) {
        return $this->userModel->getUserByUuid($uuid);
    }

    public function updateUser($uuid, $data) {
        $fields = [];
        if (isset($data['email'])) {
            if ($this->userModel->existsByEmail($data['email'])) {
                return ['status' => 409, 'message' => 'Email is already in use'];
            }
            $fields['email'] = $data['email'];
        }
        if (isset($data['username'])) {
            if ($this->userModel->existsByUsername($data['username'])) {
                return ['status' => 409, 'message' => 'Username is already in use'];
            }
            $fields['username'] = $data['username'];
        }
    
        if (empty($fields)) {
            return ['status' => 400, 'message' => 'No fields to update'];
        }
    
        $updated = $this->userModel->updateUser($uuid, $fields);
        if ($updated) {
            return ['status' => 200, 'message' => 'User updated successfully'];
        }
    
        return ['status' => 500, 'message' => 'Failed to update user'];
    }
    

    public function deleteUser($uuid) {
        if (!$this->userModel->existsByUuid($uuid)) {
            return ['status' => 404, 'message' => 'User not found'];
        }
    
        $deleted = $this->userModel->deleteUser($uuid);
        if ($deleted) {
            return ['status' => 200, 'message' => 'User deleted successfully'];
        }
    
        return ['status' => 500, 'message' => 'Failed to delete user'];
    }
    
}
?>
