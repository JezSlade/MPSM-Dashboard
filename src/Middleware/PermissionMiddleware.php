<?php
/**
 * Permission Middleware
 * Enforces permission-based access control on routes
 *
 * Usage in routes:
 * PermissionMiddleware::check(Permission::MANAGE_DEVICES);
 */

class PermissionMiddleware
{
    /**
     * Check if current user has permission
     *
     * @throws UnauthorizedException
     */
    public static function check(string $permission): void
    {
        $acl = acl();

        if (!$acl->isAuthenticated()) {
            http_response_code(401);
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Authentication required',
                'error_code' => 'UNAUTHORIZED',
            ]);
            exit;
        }

        if (!$acl->can($permission)) {
            http_response_code(403);
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Permission denied',
                'error_code' => 'FORBIDDEN',
                'required_permission' => $permission,
            ]);
            exit;
        }
    }

    /**
     * Check if user has any of the permissions
     *
     * @throws UnauthorizedException
     */
    public static function checkAny(array $permissions): void
    {
        $acl = acl();

        if (!$acl->isAuthenticated()) {
            http_response_code(401);
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Authentication required',
                'error_code' => 'UNAUTHORIZED',
            ]);
            exit;
        }

        if (!$acl->canAny($permissions)) {
            http_response_code(403);
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Permission denied',
                'error_code' => 'FORBIDDEN',
                'required_permissions' => $permissions,
            ]);
            exit;
        }
    }

    /**
     * Check if user has role
     */
    public static function checkRole(string $role): void
    {
        $acl = acl();

        if (!$acl->isAuthenticated()) {
            http_response_code(401);
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Authentication required',
                'error_code' => 'UNAUTHORIZED',
            ]);
            exit;
        }

        if (!$acl->hasRole($role)) {
            http_response_code(403);
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Insufficient privileges',
                'error_code' => 'FORBIDDEN',
                'required_role' => $role,
            ]);
            exit;
        }
    }

    /**
     * Check if user is admin or higher
     */
    public static function requireAdmin(): void
    {
        self::checkRole(Role::ADMIN);
    }

    /**
     * Check if user is super admin
     */
    public static function requireSuperAdmin(): void
    {
        self::checkRole(Role::SUPER_ADMIN);
    }
}
