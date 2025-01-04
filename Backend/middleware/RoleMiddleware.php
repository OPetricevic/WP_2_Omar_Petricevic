<?php
include_once __DIR__ . '/../config/db.php';

class RoleMiddleware {
    public function requireRole($decodedToken, $requiredRole) {
        $userRole = $decodedToken->role;
        error_log("User role from token: $userRole. Required role: $requiredRole.");

        // Fetch inherited roles
        $allowedRoles = $this->getInheritedRoles($userRole);
        error_log("Allowed roles for user: " . json_encode($allowedRoles));

        if (!in_array($requiredRole, $allowedRoles)) {
            error_log("Error: User role $userRole does not have the required role $requiredRole.");
            http_response_code(403);
            echo json_encode(['message' => 'Forbidden. You do not have access to this resource.']);
            exit;
        }

        error_log("Access granted. User role $userRole has the required role $requiredRole.");
    }

    private function getInheritedRoles($roleId) {
        global $conn;

        try {
            $stmt = $conn->prepare("SELECT inherited_ids FROM role_permissions WHERE role_id = :roleId");
            $stmt->bindParam(':roleId', $roleId, PDO::PARAM_INT);
            $stmt->execute();

            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($result && $result['inherited_ids']) {
                $inheritedIds = explode(',', $result['inherited_ids']);
                $inheritedIds[] = $roleId; // Include current role
                error_log("Inherited roles for role_id $roleId: " . json_encode($inheritedIds));
                return array_map('intval', $inheritedIds);
            }

            error_log("No inherited roles found for role_id $roleId.");
            return [$roleId];
        } catch (PDOException $e) {
            error_log("Database error fetching inherited roles: " . $e->getMessage());
            throw $e;
        }
    }
}
?>