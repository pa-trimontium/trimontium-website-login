<?php
/**
 * Plugin Name: Trimontium Website Login
 * Plugin URI: https://trimontium.com
 * Description: Secure private area for Azure and Databricks dashboards with role-based access control
 * Version: 1.0.0
 * Author: Trimontium
 * Author URI: https://trimontium.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: trimontium-website-login
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('TPA_VERSION', '1.0.0');
define('TPA_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('TPA_PLUGIN_URL', plugin_dir_url(__FILE__));
define('TPA_PLUGIN_BASENAME', plugin_basename(__FILE__));

/**
 * Main Plugin Class
 */
class Trimontium_Website_Login {

    /**
     * Singleton instance
     */
    private static $instance = null;

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
        $this->load_dependencies();
        $this->define_hooks();
    }

    /**
     * Load required dependencies
     */
    private function load_dependencies() {
        require_once TPA_PLUGIN_DIR . 'includes/class-tpa-roles.php';
        require_once TPA_PLUGIN_DIR . 'includes/class-tpa-auth.php';
        require_once TPA_PLUGIN_DIR . 'includes/class-tpa-dashboard.php';
        require_once TPA_PLUGIN_DIR . 'includes/class-tpa-api.php';
        require_once TPA_PLUGIN_DIR . 'admin/class-tpa-admin.php';
    }

    /**
     * Define WordPress hooks
     */
    private function define_hooks() {
        // Initialize components
        add_action('plugins_loaded', array($this, 'init'));

        // Enqueue scripts and styles
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_assets'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
    }

    /**
     * Initialize plugin components
     */
    public function init() {
        // Initialize role manager
        TPA_Roles::get_instance();

        // Initialize authentication
        TPA_Auth::get_instance();

        // Initialize dashboard system
        TPA_Dashboard::get_instance();

        // Initialize API handler
        TPA_API::get_instance();

        // Initialize admin interface
        if (is_admin()) {
            TPA_Admin::get_instance();
        }
    }

    /**
     * Enqueue frontend assets
     */
    public function enqueue_frontend_assets() {
        global $post;

        // Check if this is a private area page OR if the page contains our shortcodes
        $should_enqueue = false;

        if (TPA_Auth::is_private_area_page()) {
            $should_enqueue = true;
        } elseif ($post && has_shortcode($post->post_content, 'tpa_databricks_file')) {
            $should_enqueue = true;
        } elseif ($post && has_shortcode($post->post_content, 'tpa_databricks_widget')) {
            $should_enqueue = true;
        } elseif ($post && has_shortcode($post->post_content, 'tpa_azure_widget')) {
            $should_enqueue = true;
        } elseif ($post && has_shortcode($post->post_content, 'tpa_dashboard')) {
            $should_enqueue = true;
        }

        if ($should_enqueue) {
            wp_enqueue_style(
                'tpa-frontend',
                TPA_PLUGIN_URL . 'assets/css/frontend.css',
                array(),
                TPA_VERSION
            );

            wp_enqueue_script(
                'tpa-frontend',
                TPA_PLUGIN_URL . 'assets/js/frontend.js',
                array('jquery'),
                TPA_VERSION,
                true
            );

            // Localize script with AJAX URL and nonce
            wp_localize_script('tpa-frontend', 'tpaAjax', array(
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('tpa_ajax_nonce')
            ));
        }
    }

    /**
     * Enqueue admin assets
     */
    public function enqueue_admin_assets($hook) {
        // Only load on our plugin's admin pages
        if (strpos($hook, 'trimontium-website-login') !== false) {
            wp_enqueue_style(
                'tpa-admin',
                TPA_PLUGIN_URL . 'assets/css/admin.css',
                array(),
                TPA_VERSION
            );

            wp_enqueue_script(
                'tpa-admin',
                TPA_PLUGIN_URL . 'assets/js/admin.js',
                array('jquery'),
                TPA_VERSION,
                true
            );
        }
    }
}

/**
 * Plugin activation callback
 */
function trimontium_website_login_activate() {
    // Load dependencies first
    require_once TPA_PLUGIN_DIR . 'includes/class-tpa-roles.php';

    // Create custom role and capabilities
    TPA_Roles::create_private_area_role();

    // Create database tables
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();
    $table_name = $wpdb->prefix . 'tpa_api_logs';

    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        user_id bigint(20) NOT NULL,
        api_endpoint varchar(255) NOT NULL,
        request_data longtext,
        response_data longtext,
        status_code int(11),
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY  (id),
        KEY user_id (user_id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);

    // Flush rewrite rules
    flush_rewrite_rules();
}

/**
 * Plugin deactivation callback
 */
function trimontium_website_login_deactivate() {
    // Flush rewrite rules
    flush_rewrite_rules();
}

// Register activation/deactivation hooks
register_activation_hook(__FILE__, 'trimontium_website_login_activate');
register_deactivation_hook(__FILE__, 'trimontium_website_login_deactivate');

// Initialize the plugin
function trimontium_website_login() {
    return Trimontium_Website_Login::get_instance();
}

// Start the plugin
trimontium_website_login();
