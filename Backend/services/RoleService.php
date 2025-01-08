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
            // Provjera da li korisnik postoji
            if (!$this->userModel->existsByUuid($userUuid)) {
                return ['status' => 404, 'message' => 'User not found.'];
            }

            // Provjera da li role postoji u `role_permissions` tabeli
            if (!$this->roleExists($newRole)) {
                return ['status' => 400, 'message' => 'Invalid role specified.'];
            }

            // Ažuriranje role korisnika
            $this->userModel->updateUserRole($userUuid, $newRole);
            return ['status' => 200, 'message' => 'User role updated successfully.'];
        } catch (Exception $e) {
            error_log("Error in changeRole: " . $e->getMessage());
            return ['status' => 500, 'message' => 'Internal server error.'];
        }
    }

    private function roleExists($roleId) {
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM role_permissions WHERE role_id = :roleId");
        $stmt->bindParam(':roleId', $roleId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchColumn() > 0;
    }
    
    
    public function submitRoleChangeRequest($userUuid, $requestedRole) {
        try {
            // Check for existing pending requests for the same user and role
            $stmt = $this->conn->prepare("
                SELECT COUNT(*) FROM role_permission_requests 
                WHERE user_uuid = :user_uuid AND requested_role = :requested_role AND status = 'pending'
            ");
            $stmt->execute([':user_uuid' => $userUuid, ':requested_role' => $requestedRole]);
            if ($stmt->fetchColumn() > 0) {
                return ['status' => 409, 'message' => 'A pending request for this role already exists.'];
            }
    
            // If no pending requests, insert a new one
            $uuid = uniqid();
            $stmt = $this->conn->prepare("
                INSERT INTO role_permission_requests (uuid, user_uuid, requested_role)
                VALUES (:uuid, :user_uuid, :requested_role)
            ");
            $stmt->execute([
                ':uuid' => $uuid,
                ':user_uuid' => $userUuid,
                ':requested_role' => $requestedRole
            ]);
    
            return ['status' => 201, 'message' => 'Role change request submitted successfully.'];
        } catch (Exception $e) {
            error_log("Error in submitRoleChangeRequest: " . $e->getMessage());
            return ['status' => 500, 'message' => 'Internal server error.'];
        }
    }
    
    
    public function reviewRoleChangeRequest($requestUuid, $action) {
        try {
            // Dohvati zahtjev
            $stmt = $this->conn->prepare("SELECT * FROM role_permission_requests WHERE uuid = :uuid");
            $stmt->execute([':uuid' => $requestUuid]);
            $request = $stmt->fetch(PDO::FETCH_ASSOC);
    
            if (!$request) {
                return ['status' => 404, 'message' => 'Role change request not found.'];
            }
    
            // Ažuriraj status zahtjeva
            $newStatus = $action === 'approve' ? 'approved' : 'rejected';
            $stmt = $this->conn->prepare("
                UPDATE role_permission_requests
                SET status = :status, updated_at = NOW()
                WHERE uuid = :uuid
            ");
            $stmt->execute([':status' => $newStatus, ':uuid' => $requestUuid]);
    
            // Ako je zahtjev odobren, promijeni rolu korisnika
            if ($action === 'approve') {
                $this->userModel->updateUserRole($request['user_uuid'], $request['requested_role']);
            }
    
            return ['status' => 200, 'message' => 'Role change request ' . $newStatus . '.'];
        } catch (Exception $e) {
            error_log("Error in reviewRoleChangeRequest: " . $e->getMessage());
            return ['status' => 500, 'message' => 'Internal server error.'];
        }
    }

    public function getPendingRoleChangeRequests() {
        try {
            $stmt = $this->conn->prepare("
                SELECT * FROM role_permission_requests
                WHERE status = 'pending'
            ");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error in getPendingRoleChangeRequests(): " . $e->getMessage());
            throw $e;
        }
    }
    
    
}
?>
