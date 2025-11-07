<?php
/**
 * Access Control Manager
 * Centralized permission checking and enforcement
 */

class AccessControl
{
    private ?array $currentUser = null;

    /**
     * Set current user
     */
    public function setUser(?array $user): void
    {
        $this->currentUser = $user;
    }

    /**
     * Get current user
     */
    public function getUser(): ?array
    {
        return $this->currentUser;
    }

    /**
     * Get current user role
     */
    public function getUserRole(): ?string
    {
        return $this->currentUser['role'] ?? null;
    }

    /**
     * Check if user has permission
     */
    public function can(string $permission): bool
    {
        if (!$this->currentUser) {
            return false;
        }

        $role = $this->getUserRole();
        if (!$role) {
            return false;
        }

        return Role::hasPermission($role, $permission);
    }

    /**
     * Check if user has any of the given permissions
     */
    public function canAny(array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if ($this->can($permission)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if user has all of the given permissions
     */
    public function canAll(array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if (!$this->can($permission)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Require permission or throw exception
     *
     * @throws UnauthorizedException
     */
    public function require(string $permission): void
    {
        if (!$this->can($permission)) {
            throw new UnauthorizedException(
                "Permission denied: {$permission}"
            );
        }
    }

    /**
     * Require any of the permissions or throw exception
     *
     * @throws UnauthorizedException
     */
    public function requireAny(array $permissions): void
    {
        if (!$this->canAny($permissions)) {
            throw new UnauthorizedException(
                'Permission denied: ' . implode(', ', $permissions)
            );
        }
    }

    /**
     * Check if user has specific role
     */
    public function hasRole(string $role): bool
    {
        return $this->getUserRole() === $role;
    }

    /**
     * Check if user has any of the roles
     */
    public function hasAnyRole(array $roles): bool
    {
        $userRole = $this->getUserRole();
        return in_array($userRole, $roles, true);
    }

    /**
     * Check if user role is higher than given role
     */
    public function isRoleHigherThan(string $role): bool
    {
        $userRole = $this->getUserRole();
        if (!$userRole) {
            return false;
        }

        return Role::isHigherThan($userRole, $role);
    }

    /**
     * Check if user is viewer
     */
    public function isViewer(): bool
    {
        return $this->hasRole(Role::VIEWER);
    }

    /**
     * Check if user is analyst or higher
     */
    public function isAnalyst(): bool
    {
        return $this->hasAnyRole([Role::ANALYST, Role::ADMIN, Role::SUPER_ADMIN]);
    }

    /**
     * Check if user is admin or higher
     */
    public function isAdmin(): bool
    {
        return $this->hasAnyRole([Role::ADMIN, Role::SUPER_ADMIN]);
    }

    /**
     * Check if user is super admin
     */
    public function isSuperAdmin(): bool
    {
        return $this->hasRole(Role::SUPER_ADMIN);
    }

    /**
     * Get user permissions
     */
    public function getPermissions(): array
    {
        $role = $this->getUserRole();
        if (!$role) {
            return [];
        }

        return Role::getPermissions($role);
    }

    /**
     * Check if user is authenticated
     */
    public function isAuthenticated(): bool
    {
        return $this->currentUser !== null;
    }
}

/**
 * Unauthorized Exception
 */
class UnauthorizedException extends Exception
{
}

// Global access control instance
function acl(): AccessControl
{
    static $acl = null;
    if ($acl === null) {
        $acl = new AccessControl();
    }
    return $acl;
}
