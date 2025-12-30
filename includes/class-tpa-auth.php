<?php
/**
 * Authentication and Access Control Class
 * Handles secure authentication and page access restrictions
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class TPA_Auth {

    /**
     * Singleton instance
     */
    private static $instance = null;

    /**
     * Private area page slug prefix
     */
    const PAGE_SLUG_PREFIX = 'private-';

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
        $this->init_hooks();
    }

    /**
     * Initialize WordPress hooks
     */
    private function init_hooks() {
        // Check access on template redirect
        add_action('template_redirect', array($this, 'check_page_access'));

        // Add body class for private pages
        add_filter('body_class', array($this, 'add_body_class'));

        // Secure REST API endpoints
        add_filter('rest_authentication_errors', array($this, 'rest_authentication_check'));

        // Add login/logout redirects
        add_filter('login_redirect', array($this, 'login_redirect'), 10, 3);
        add_action('wp_logout', array($this, 'logout_redirect'));
    }

    /**
     * Check if current page is a private area page
     */
    public static function is_private_area_page() {
        global $post;

        if (!$post) {
            return false;
        }

        // Check if page slug starts with our prefix
        if (strpos($post->post_name, self::PAGE_SLUG_PREFIX) === 0) {
            return true;
        }

        // Check if page has private area meta
        $is_private = get_post_meta($post->ID, '_tpa_is_private', true);
        return $is_private === '1';
    }

    /**
     * Check page access and redirect if unauthorized
     */
    public function check_page_access() {
        if (!self::is_private_area_page()) {
            return;
        }

        // Check if user is logged in
        if (!is_user_logged_in()) {
            $this->redirect_to_login();
            exit;
        }

        // Check if user has required capability
        if (!TPA_Roles::user_can_access_private_area()) {
            $this->show_access_denied();
            exit;
        }

        // Page-specific access control
        $this->check_dashboard_specific_access();
    }

    /**
     * Check dashboard-specific access (Azure vs Databricks)
     */
    private function check_dashboard_specific_access() {
        global $post;

        if (!$post) {
            return;
        }

        $dashboard_type = get_post_meta($post->ID, '_tpa_dashboard_type', true);

        if ($dashboard_type === 'azure' && !TPA_Roles::user_can_access_azure()) {
            $this->show_access_denied('You do not have permission to access Azure dashboards.');
            exit;
        }

        if ($dashboard_type === 'databricks' && !TPA_Roles::user_can_access_databricks()) {
            $this->show_access_denied('You do not have permission to access Databricks dashboards.');
            exit;
        }
    }

    /**
     * Redirect to login page
     */
    private function redirect_to_login() {
        $redirect_url = get_permalink();
        $login_url = wp_login_url($redirect_url);

        wp_safe_redirect($login_url);
        exit;
    }

    /**
     * Show access denied page
     */
    private function show_access_denied($message = null) {
        if (!$message) {
            $message = __('You do not have permission to access this page.', 'trimontium-website-login');
        }

        wp_die(
            esc_html($message),
            __('Access Denied', 'trimontium-website-login'),
            array(
                'response' => 403,
                'back_link' => true
            )
        );
    }

    /**
     * Add body class for private pages
     */
    public function add_body_class($classes) {
        if (self::is_private_area_page()) {
            $classes[] = 'private-area-page';

            if (TPA_Roles::user_can_access_private_area()) {
                $classes[] = 'private-area-authorized';
            }
        }

        return $classes;
    }

    /**
     * Secure REST API endpoints
     */
    public function rest_authentication_check($result) {
        // If a previous authentication check was applied, respect it
        if (true === $result || is_wp_error($result)) {
            return $result;
        }

        // Check if this is a private area API endpoint
        $request_uri = $_SERVER['REQUEST_URI'] ?? '';
        if (strpos($request_uri, '/wp-json/tpa/v1/') !== false) {
            if (!is_user_logged_in()) {
                return new WP_Error(
                    'rest_not_logged_in',
                    __('You are not currently logged in.', 'trimontium-website-login'),
                    array('status' => 401)
                );
            }

            if (!TPA_Roles::user_can_access_private_area()) {
                return new WP_Error(
                    'rest_forbidden',
                    __('You do not have permission to access this resource.', 'trimontium-website-login'),
                    array('status' => 403)
                );
            }
        }

        return $result;
    }

    /**
     * Redirect after login
     */
    public function login_redirect($redirect_to, $request, $user) {
        // If user doesn't have private area access, send to home
        if (isset($user->ID) && !TPA_Roles::user_can_access_private_area($user->ID)) {
            return home_url();
        }

        // If there's a redirect_to parameter, use it
        if ($redirect_to) {
            return $redirect_to;
        }

        // Otherwise, redirect to private area dashboard
        $dashboard_page = get_option('tpa_main_dashboard_page');
        if ($dashboard_page) {
            return get_permalink($dashboard_page);
        }

        return admin_url();
    }

    /**
     * Redirect after logout
     */
    public function logout_redirect() {
        wp_safe_redirect(home_url());
        exit;
    }

    /**
     * Verify nonce for AJAX requests
     */
    public static function verify_ajax_nonce() {
        if (!check_ajax_referer('tpa_ajax_nonce', 'nonce', false)) {
            wp_send_json_error(array(
                'message' => __('Security check failed.', 'trimontium-website-login')
            ), 403);
            exit;
        }

        if (!is_user_logged_in()) {
            wp_send_json_error(array(
                'message' => __('You must be logged in.', 'trimontium-website-login')
            ), 401);
            exit;
        }

        if (!TPA_Roles::user_can_access_private_area()) {
            wp_send_json_error(array(
                'message' => __('You do not have permission to perform this action.', 'trimontium-website-login')
            ), 403);
            exit;
        }

        return true;
    }

    /**
     * Generate secure token for API requests
     */
    public static function generate_api_token($user_id = null) {
        if (!$user_id) {
            $user_id = get_current_user_id();
        }

        $token = wp_generate_password(32, false);
        $hashed_token = wp_hash_password($token);

        update_user_meta($user_id, '_tpa_api_token', $hashed_token);
        update_user_meta($user_id, '_tpa_api_token_created', time());

        return $token;
    }

    /**
     * Verify API token
     */
    public static function verify_api_token($token, $user_id) {
        $stored_hash = get_user_meta($user_id, '_tpa_api_token', true);

        if (!$stored_hash) {
            return false;
        }

        return wp_check_password($token, $stored_hash);
    }

    /**
     * Log access attempt
     */
    public static function log_access_attempt($user_id, $page_id, $success = true) {
        $log_entry = array(
            'user_id' => $user_id,
            'page_id' => $page_id,
            'timestamp' => current_time('mysql'),
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'success' => $success
        );

        // Store in transient (expires after 7 days)
        $log_key = 'tpa_access_log_' . $user_id;
        $existing_log = get_transient($log_key) ?: array();
        $existing_log[] = $log_entry;

        // Keep only last 100 entries per user
        if (count($existing_log) > 100) {
            $existing_log = array_slice($existing_log, -100);
        }

        set_transient($log_key, $existing_log, 7 * DAY_IN_SECONDS);
    }
}
