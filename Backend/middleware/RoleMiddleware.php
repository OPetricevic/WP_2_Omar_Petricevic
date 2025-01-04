<?php
class RoleMiddleware {
    public function requireRole($decodedToken, $requiredRoles) {
        if (!in_array($decodedToken->role, $requiredRoles)) {
            http_response_code(403);
            echo json_encode(['message' => 'Forbidden. You do not have access to this resource.']);
            exit;
        }
    }
}
?>
