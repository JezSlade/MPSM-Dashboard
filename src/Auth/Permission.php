<?php
/**
 * Permission System
 * Defines all available permissions in the system
 *
 * 4 Access Levels:
 * - Viewer: Read-only access to dashboard and devices
 * - Analyst: Viewer + panel messages and reports
 * - Admin: Analyst + device management and cache control
 * - Super Admin: Admin + user management and system settings
 */

class Permission
{
    // Dashboard & Devices (All levels)
    const VIEW_DASHBOARD = 'view_dashboard';
    const VIEW_DEVICES = 'view_devices';
    const VIEW_DEVICE_DETAILS = 'view_device_details';

    // Panel Messages (Analyst+)
    const VIEW_PANEL_MESSAGES = 'view_panel_messages';
    const VIEW_MESSAGE_HISTORY = 'view_message_history';

    // Reports & Analytics (Analyst+)
    const VIEW_REPORTS = 'view_reports';
    const EXPORT_DATA = 'export_data';

    // Device Management (Admin+)
    const MANAGE_DEVICES = 'manage_devices';
    const CREATE_DEVICE = 'create_device';
    const UPDATE_DEVICE = 'update_device';
    const DELETE_DEVICE = 'delete_device';

    // Cache Control (Admin+)
    const REFRESH_CACHE = 'refresh_cache';
    const CLEAR_CACHE = 'clear_cache';
    const VIEW_CACHE_STATS = 'view_cache_stats';

    // Job Queue (Admin+)
    const VIEW_JOBS = 'view_jobs';
    const MANAGE_JOBS = 'manage_jobs';
    const RETRY_JOBS = 'retry_jobs';

    // User Management (Super Admin only)
    const VIEW_USERS = 'view_users';
    const CREATE_USER = 'create_user';
    const UPDATE_USER = 'update_user';
    const DELETE_USER = 'delete_user';
    const MANAGE_ROLES = 'manage_roles';

    // System Settings (Super Admin only)
    const VIEW_SETTINGS = 'view_settings';
    const UPDATE_SETTINGS = 'update_settings';
    const VIEW_LOGS = 'view_logs';
    const MANAGE_API_KEYS = 'manage_api_keys';

    /**
     * Get all permissions
     */
    public static function all(): array
    {
        $reflection = new ReflectionClass(__CLASS__);
        return $reflection->getConstants();
    }

    /**
     * Get permissions by category
     */
    public static function byCategory(): array
    {
        return [
            'Dashboard & Devices' => [
                self::VIEW_DASHBOARD,
                self::VIEW_DEVICES,
                self::VIEW_DEVICE_DETAILS,
            ],
            'Panel Messages' => [
                self::VIEW_PANEL_MESSAGES,
                self::VIEW_MESSAGE_HISTORY,
            ],
            'Reports & Analytics' => [
                self::VIEW_REPORTS,
                self::EXPORT_DATA,
            ],
            'Device Management' => [
                self::MANAGE_DEVICES,
                self::CREATE_DEVICE,
                self::UPDATE_DEVICE,
                self::DELETE_DEVICE,
            ],
            'Cache Control' => [
                self::REFRESH_CACHE,
                self::CLEAR_CACHE,
                self::VIEW_CACHE_STATS,
            ],
            'Job Queue' => [
                self::VIEW_JOBS,
                self::MANAGE_JOBS,
                self::RETRY_JOBS,
            ],
            'User Management' => [
                self::VIEW_USERS,
                self::CREATE_USER,
                self::UPDATE_USER,
                self::DELETE_USER,
                self::MANAGE_ROLES,
            ],
            'System Settings' => [
                self::VIEW_SETTINGS,
                self::UPDATE_SETTINGS,
                self::VIEW_LOGS,
                self::MANAGE_API_KEYS,
            ],
        ];
    }
}
