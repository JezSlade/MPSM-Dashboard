<?php
/**
 * Role System
 * Defines the 4 access levels and their permissions
 *
 * Hierarchy (each level inherits from previous):
 * 1. Viewer (Base level)
 * 2. Analyst (Viewer + analysis tools)
 * 3. Admin (Analyst + management)
 * 4. Super Admin (Admin + system control)
 */

class Role
{
    const VIEWER = 'viewer';
    const ANALYST = 'analyst';
    const ADMIN = 'admin';
    const SUPER_ADMIN = 'super_admin';

    /**
     * Role hierarchy (lower number = lower privilege)
     */
    private static array $hierarchy = [
        self::VIEWER => 1,
        self::ANALYST => 2,
        self::ADMIN => 3,
        self::SUPER_ADMIN => 4,
    ];

    /**
     * Get permissions for a role
     */
    public static function getPermissions(string $role): array
    {
        switch ($role) {
            case self::VIEWER:
                return self::getViewerPermissions();

            case self::ANALYST:
                return array_merge(
                    self::getViewerPermissions(),
                    self::getAnalystPermissions()
                );

            case self::ADMIN:
                return array_merge(
                    self::getViewerPermissions(),
                    self::getAnalystPermissions(),
                    self::getAdminPermissions()
                );

            case self::SUPER_ADMIN:
                return array_merge(
                    self::getViewerPermissions(),
                    self::getAnalystPermissions(),
                    self::getAdminPermissions(),
                    self::getSuperAdminPermissions()
                );

            default:
                return [];
        }
    }

    /**
     * Viewer permissions (base level)
     */
    private static function getViewerPermissions(): array
    {
        return [
            Permission::VIEW_DASHBOARD,
            Permission::VIEW_DEVICES,
            Permission::VIEW_DEVICE_DETAILS,
        ];
    }

    /**
     * Analyst permissions (added to Viewer)
     */
    private static function getAnalystPermissions(): array
    {
        return [
            Permission::VIEW_PANEL_MESSAGES,
            Permission::VIEW_MESSAGE_HISTORY,
            Permission::VIEW_REPORTS,
            Permission::EXPORT_DATA,
        ];
    }

    /**
     * Admin permissions (added to Analyst)
     */
    private static function getAdminPermissions(): array
    {
        return [
            Permission::MANAGE_DEVICES,
            Permission::CREATE_DEVICE,
            Permission::UPDATE_DEVICE,
            Permission::DELETE_DEVICE,
            Permission::REFRESH_CACHE,
            Permission::CLEAR_CACHE,
            Permission::VIEW_CACHE_STATS,
            Permission::VIEW_JOBS,
            Permission::MANAGE_JOBS,
            Permission::RETRY_JOBS,
        ];
    }

    /**
     * Super Admin permissions (added to Admin)
     */
    private static function getSuperAdminPermissions(): array
    {
        return [
            Permission::VIEW_USERS,
            Permission::CREATE_USER,
            Permission::UPDATE_USER,
            Permission::DELETE_USER,
            Permission::MANAGE_ROLES,
            Permission::VIEW_SETTINGS,
            Permission::UPDATE_SETTINGS,
            Permission::VIEW_LOGS,
            Permission::MANAGE_API_KEYS,
        ];
    }

    /**
     * Check if role has permission
     */
    public static function hasPermission(string $role, string $permission): bool
    {
        $permissions = self::getPermissions($role);
        return in_array($permission, $permissions, true);
    }

    /**
     * Check if role1 is higher than role2
     */
    public static function isHigherThan(string $role1, string $role2): bool
    {
        $level1 = self::$hierarchy[$role1] ?? 0;
        $level2 = self::$hierarchy[$role2] ?? 0;

        return $level1 > $level2;
    }

    /**
     * Get all roles
     */
    public static function all(): array
    {
        return [
            self::VIEWER,
            self::ANALYST,
            self::ADMIN,
            self::SUPER_ADMIN,
        ];
    }

    /**
     * Get role display name
     */
    public static function getDisplayName(string $role): string
    {
        $names = [
            self::VIEWER => 'Viewer',
            self::ANALYST => 'Analyst',
            self::ADMIN => 'Admin',
            self::SUPER_ADMIN => 'Super Admin',
        ];

        return $names[$role] ?? ucfirst($role);
    }

    /**
     * Get role description
     */
    public static function getDescription(string $role): string
    {
        $descriptions = [
            self::VIEWER => 'Read-only access to dashboard and devices',
            self::ANALYST => 'Viewer access plus panel messages and reports',
            self::ADMIN => 'Analyst access plus device and cache management',
            self::SUPER_ADMIN => 'Full system access including user and settings management',
        ];

        return $descriptions[$role] ?? '';
    }

    /**
     * Validate role exists
     */
    public static function isValid(string $role): bool
    {
        return isset(self::$hierarchy[$role]);
    }
}
