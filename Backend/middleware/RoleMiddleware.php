<?php
include_once __DIR__ . '/../config/db.php';

class RoleMiddleware {
    public function requireRole($decodedToken, $requiredRole) {
        $userRole = $decodedToken->role;

        // Dobavi naslijeđene uloge
        $allowedRoles = $this->getInheritedRoles($userRole);

        // Provjera da li je tražena uloga među dozvoljenim
        if (!in_array($requiredRole, $allowedRoles)) {
            http_response_code(403);
            echo json_encode(['message' => 'Forbidden. You do not have access to this resource.']);
            exit;
        }
    }

    private function getInheritedRoles($roleId) {
        global $conn;

        // Dohvati naslijeđene uloge iz tabele `role_permissions`
        $stmt = $conn->prepare("SELECT inherited_ids FROM role_permissions WHERE role_id = :roleId");
        $stmt->bindParam(':roleId', $roleId, PDO::PARAM_INT);
        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result && $result['inherited_ids']) {
            $inheritedIds = explode(',', $result['inherited_ids']);
            $inheritedIds[] = $roleId; // Dodaj trenutnu ulogu
            return array_map('intval', $inheritedIds);
        }

        return [$roleId]; // Vraća samo trenutnu ulogu ako nema nasljeđivanja
    }
}
?>
