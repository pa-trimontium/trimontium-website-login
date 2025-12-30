<?php
/**
 * Role Management Class
 * Handles custom roles and capabilities for private area access
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class TPA_Roles {

    /**
     * Singleton instance
     */
    private static $instance = null;

    /**
     * Custom role name
     */
    const ROLE_NAME = 'private_area_user';

    /**
     * Get singleton instance
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct() {
        // Add hooks if needed
    }

    /**
     * Create the private area user role
     */
    public static function create_private_area_role() {
        // Remove role if it exists (for clean reinstall)
        remove_role(self::ROLE_NAME);

        // Add the role with capabilities
        add_role(
            self::ROLE_NAME,
            __('Private Area User', 'trimontium-website-login'),
            array(
                'read'                      => true,  // Basic read access
                'access_private_area'       => true,  // Custom capability
                'view_azure_dashboards'     => true,  // Azure access
                'view_databricks_dashboards' => true,  // Databricks access
            )
        );

        // Add capabilities to administrator role as well
        $admin_role = get_role('administrator');
        if ($admin_role) {
            $admin_role->add_cap('access_private_area');
            $admin_role->add_cap('view_azure_dashboards');
            $admin_role->add_cap('view_databricks_dashboards');
            $admin_role->add_cap('manage_private_area_settings');
        }
    }

    /**
     * Remove the private area user role
     */
    public static function remove_private_area_role() {
        remove_role(self::ROLE_NAME);

        // Remove capabilities from administrator
        $admin_role = get_role('administrator');
        if ($admin_role) {
            $admin_role->remove_cap('access_private_area');
            $admin_role->remove_cap('view_azure_dashboards');
            $admin_role->remove_cap('view_databricks_dashboards');
            $admin_role->remove_cap('manage_private_area_settings');
        }
    }

    /**
     * Check if user has access to private area
     */
    public static function user_can_access_private_area($user_id = null) {
        if (!$user_id) {
            $user_id = get_current_user_id();
        }

        if (!$user_id) {
            return false;
        }

        $user = get_user_by('id', $user_id);
        if (!$user) {
            return false;
        }

        return user_can($user, 'access_private_area');
    }

    /**
     * Check if user has access to Azure dashboards
     */
    public static function user_can_access_azure($user_id = null) {
        if (!$user_id) {
            $user_id = get_current_user_id();
        }

        if (!$user_id) {
            return false;
        }

        $user = get_user_by('id', $user_id);
        if (!$user) {
            return false;
        }

        return user_can($user, 'view_azure_dashboards');
    }

    /**
     * Check if user has access to Databricks dashboards
     */
    public static function user_can_access_databricks($user_id = null) {
        if (!$user_id) {
            $user_id = get_current_user_id();
        }

        if (!$user_id) {
            return false;
        }

        $user = get_user_by('id', $user_id);
        if (!$user) {
            return false;
        }

        return user_can($user, 'view_databricks_dashboards');
    }

    /**
     * Get all users with private area access
     */
    public static function get_private_area_users() {
        $args = array(
            'role__in' => array(self::ROLE_NAME, 'administrator'),
            'orderby'  => 'display_name',
            'order'    => 'ASC'
        );

        return get_users($args);
    }

    /**
     * Assign private area role to user
     */
    public static function assign_private_area_access($user_id) {
        $user = get_user_by('id', $user_id);
        if (!$user) {
            return false;
        }

        $user->add_role(self::ROLE_NAME);
        return true;
    }

    /**
     * Remove private area role from user
     */
    public static function remove_private_area_access($user_id) {
        $user = get_user_by('id', $user_id);
        if (!$user) {
            return false;
        }

        $user->remove_role(self::ROLE_NAME);
        return true;
    }
}
