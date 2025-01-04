<?php
include_once __DIR__ . '/../models/User.php';
include_once __DIR__ . '/../config/db.php';

class RoleService {
    private $conn;
    private $userModel;

    public function __construct() {
        global $conn;
        $this->conn = $conn;
        $this->userModel = new User();
    }

    public function changeRole($userUuid, $newRole) {
        try {
            // Proveri da li korisnik postoji
            if (!$this->userModel->existsByUuid($userUuid)) {
                return ['status' => 404, 'message' => 'User not found.'];
            }

            // Proveri da li role postoji u role_permissions tabeli
            if (!$this->roleExists($newRole)) {
                return ['status' => 400, 'message' => 'Invalid role specified.'];
            }

            // Ažuriraj rolu korisnika
            $this->userModel->updateUserRole($userUuid, $newRole);
            return ['status' => 200, 'message' => 'User role updated successfully.'];
        } catch (Exception $e) {
            error_log("Error in changeRole: " . $e->getMessage());
            return ['status' => 500, 'message' => 'Internal server error.'];
        }
    }

    private function roleExists($roleId) {
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM role_permissions WHERE id = :roleId");
        $stmt->bindParam(':roleId', $roleId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchColumn() > 0;
    }
}
?>
