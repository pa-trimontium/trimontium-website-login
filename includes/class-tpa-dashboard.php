<?php
/**
 * Dashboard Management Class
 * Handles dashboard pages and their rendering
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class TPA_Dashboard {

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
        $this->init_hooks();
    }

    /**
     * Initialize WordPress hooks
     */
    private function init_hooks() {
        // Register custom post type for dashboards
        add_action('init', array($this, 'register_dashboard_post_type'));

        // Add meta boxes
        add_action('add_meta_boxes', array($this, 'add_dashboard_meta_boxes'));
        add_action('save_post', array($this, 'save_dashboard_meta'));

        // Use custom template for dashboard pages
        add_filter('template_include', array($this, 'load_dashboard_template'));

        // Register shortcodes
        add_shortcode('tpa_dashboard', array($this, 'dashboard_shortcode'));
        add_shortcode('tpa_azure_widget', array($this, 'azure_widget_shortcode'));
        add_shortcode('tpa_databricks_widget', array($this, 'databricks_widget_shortcode'));
        add_shortcode('tpa_databricks_file', array($this, 'databricks_file_shortcode'));

        // AJAX handlers for dashboard data
        add_action('wp_ajax_tpa_get_dashboard_data', array($this, 'ajax_get_dashboard_data'));
        add_action('wp_ajax_tpa_refresh_widget', array($this, 'ajax_refresh_widget'));
        add_action('wp_ajax_tpa_load_databricks_file', array($this, 'ajax_load_databricks_file'));
    }

    /**
     * Register custom post type for dashboards
     */
    public function register_dashboard_post_type() {
        $args = array(
            'label' => __('Private Dashboards', 'trimontium-website-login'),
            'public' => true,
            'publicly_queryable' => true,
            'show_ui' => true,
            'show_in_menu' => 'trimontium-website-login',
            'query_var' => true,
            'rewrite' => array('slug' => 'private-dashboard'),
            'capability_type' => 'page',
            'has_archive' => false,
            'hierarchical' => true,
            'menu_position' => null,
            'supports' => array('title', 'editor', 'page-attributes'),
            'show_in_rest' => true,
        );

        register_post_type('tpa_dashboard', $args);
    }

    /**
     * Add meta boxes for dashboard configuration
     */
    public function add_dashboard_meta_boxes() {
        add_meta_box(
            'tpa_dashboard_settings',
            __('Dashboard Settings', 'trimontium-website-login'),
            array($this, 'render_dashboard_settings_metabox'),
            'tpa_dashboard',
            'side',
            'high'
        );

        add_meta_box(
            'tpa_dashboard_widgets',
            __('Dashboard Widgets', 'trimontium-website-login'),
            array($this, 'render_dashboard_widgets_metabox'),
            'tpa_dashboard',
            'normal',
            'high'
        );
    }

    /**
     * Render dashboard settings meta box
     */
    public function render_dashboard_settings_metabox($post) {
        wp_nonce_field('tpa_dashboard_meta', 'tpa_dashboard_meta_nonce');

        $is_private = get_post_meta($post->ID, '_tpa_is_private', true);
        $dashboard_type = get_post_meta($post->ID, '_tpa_dashboard_type', true);

        ?>
        <p>
            <label>
                <input type="checkbox" name="tpa_is_private" value="1" <?php checked($is_private, '1'); ?>>
                <?php _e('Require authentication to view', 'trimontium-website-login'); ?>
            </label>
        </p>

        <p>
            <label for="tpa_dashboard_type">
                <strong><?php _e('Dashboard Type:', 'trimontium-website-login'); ?></strong>
            </label><br>
            <select name="tpa_dashboard_type" id="tpa_dashboard_type" style="width: 100%;">
                <option value=""><?php _e('General', 'trimontium-website-login'); ?></option>
                <option value="azure" <?php selected($dashboard_type, 'azure'); ?>><?php _e('Azure', 'trimontium-website-login'); ?></option>
                <option value="databricks" <?php selected($dashboard_type, 'databricks'); ?>><?php _e('Databricks', 'trimontium-website-login'); ?></option>
            </select>
        </p>
        <?php
    }

    /**
     * Render dashboard widgets meta box
     */
    public function render_dashboard_widgets_metabox($post) {
        $widgets = get_post_meta($post->ID, '_tpa_dashboard_widgets', true) ?: array();
        ?>
        <div id="tpa-widgets-container">
            <p><?php _e('Add widgets to your dashboard using shortcodes in the editor, or configure them here:', 'trimontium-website-login'); ?></p>

            <h4><?php _e('Available Shortcodes:', 'trimontium-website-login'); ?></h4>
            <ul style="list-style: disc; margin-left: 20px;">
                <li><code>[tpa_dashboard id="dashboard_id"]</code> - Display a complete dashboard</li>
                <li><code>[tpa_azure_widget type="metrics" resource_id="resource_id"]</code> - Azure widget</li>
                <li><code>[tpa_databricks_widget type="jobs" workspace="workspace_url"]</code> - Databricks widget</li>
            </ul>

            <h4><?php _e('Widget Examples:', 'trimontium-website-login'); ?></h4>
            <pre style="background: #f5f5f5; padding: 10px; overflow-x: auto;">
// Azure Metrics Widget
[tpa_azure_widget type="metrics" resource_id="/subscriptions/xxx/resourceGroups/xxx/providers/xxx" metric="Percentage CPU"]

// Databricks Jobs Widget
[tpa_databricks_widget type="jobs" workspace="https://adb-xxxxx.azuredatabricks.net" limit="10"]

// Custom Dashboard
[tpa_dashboard id="custom" layout="grid"]
            </pre>
        </div>
        <?php
    }

    /**
     * Save dashboard meta data
     */
    public function save_dashboard_meta($post_id) {
        // Verify nonce
        if (!isset($_POST['tpa_dashboard_meta_nonce']) ||
            !wp_verify_nonce($_POST['tpa_dashboard_meta_nonce'], 'tpa_dashboard_meta')) {
            return;
        }

        // Check autosave
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        // Check permissions
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        // Save private flag
        $is_private = isset($_POST['tpa_is_private']) ? '1' : '0';
        update_post_meta($post_id, '_tpa_is_private', $is_private);

        // Save dashboard type
        if (isset($_POST['tpa_dashboard_type'])) {
            update_post_meta($post_id, '_tpa_dashboard_type', sanitize_text_field($_POST['tpa_dashboard_type']));
        }
    }

    /**
     * Load custom template for dashboard pages
     */
    public function load_dashboard_template($template) {
        if (is_singular('tpa_dashboard')) {
            $custom_template = TPA_PLUGIN_DIR . 'templates/dashboard-template.php';
            if (file_exists($custom_template)) {
                return $custom_template;
            }
        }

        return $template;
    }

    /**
     * Dashboard shortcode handler
     */
    public function dashboard_shortcode($atts) {
        $atts = shortcode_atts(array(
            'id' => '',
            'layout' => 'default',
            'columns' => '2'
        ), $atts);

        ob_start();
        include TPA_PLUGIN_DIR . 'templates/shortcode-dashboard.php';
        return ob_get_clean();
    }

    /**
     * Azure widget shortcode handler
     */
    public function azure_widget_shortcode($atts) {
        // Check permissions
        if (!TPA_Roles::user_can_access_azure()) {
            return '<div class="tpa-error">' . __('You do not have permission to view Azure data.', 'trimontium-website-login') . '</div>';
        }

        $atts = shortcode_atts(array(
            'type' => 'metrics',
            'resource_id' => '',
            'metric' => '',
            'title' => '',
            'height' => '300px'
        ), $atts);

        ob_start();
        include TPA_PLUGIN_DIR . 'templates/widget-azure.php';
        return ob_get_clean();
    }

    /**
     * Databricks widget shortcode handler
     */
    public function databricks_widget_shortcode($atts) {
        // Check permissions
        if (!TPA_Roles::user_can_access_databricks()) {
            return '<div class="tpa-error">' . __('You do not have permission to view Databricks data.', 'trimontium-website-login') . '</div>';
        }

        $atts = shortcode_atts(array(
            'type' => 'jobs',
            'workspace' => '',
            'limit' => '10',
            'title' => '',
            'height' => '400px'
        ), $atts);

        ob_start();
        include TPA_PLUGIN_DIR . 'templates/widget-databricks.php';
        return ob_get_clean();
    }

    /**
     * Databricks file widget shortcode handler
     */
    public function databricks_file_shortcode($atts) {
        // Check permissions
        if (!TPA_Roles::user_can_access_databricks()) {
            return '<div class="tpa-error">' . __('You do not have permission to view Databricks data.', 'trimontium-website-login') . '</div>';
        }

        $atts = shortcode_atts(array(
            'file_path' => '/Volumes/db_trimontium_dev/trimontium-hot-leads/output/leads.json',
            'title' => 'Lead Viewer',
            'height' => '600px',
            'display' => 'table'  // table, json, or raw
        ), $atts);

        if (empty($atts['file_path'])) {
            return '<div class="tpa-error">' . __('File path is required.', 'trimontium-website-login') . '</div>';
        }

        ob_start();
        include TPA_PLUGIN_DIR . 'templates/widget-databricks-file.php';
        return ob_get_clean();
    }

    /**
     * AJAX handler for getting dashboard data
     */
    public function ajax_get_dashboard_data() {
        TPA_Auth::verify_ajax_nonce();

        $dashboard_id = isset($_POST['dashboard_id']) ? intval($_POST['dashboard_id']) : 0;
        $dashboard_type = isset($_POST['dashboard_type']) ? sanitize_text_field($_POST['dashboard_type']) : '';

        if (!$dashboard_id) {
            wp_send_json_error(array('message' => 'Invalid dashboard ID'));
        }

        // Get dashboard data based on type
        $data = array();

        if ($dashboard_type === 'azure') {
            $data = TPA_API::get_azure_data($dashboard_id);
        } elseif ($dashboard_type === 'databricks') {
            $data = TPA_API::get_databricks_data($dashboard_id);
        }

        wp_send_json_success($data);
    }

    /**
     * AJAX handler for refreshing a widget
     */
    public function ajax_refresh_widget() {
        TPA_Auth::verify_ajax_nonce();

        $widget_type = isset($_POST['widget_type']) ? sanitize_text_field($_POST['widget_type']) : '';
        $widget_config = isset($_POST['widget_config']) ? $_POST['widget_config'] : array();

        $data = array();

        if ($widget_type === 'azure') {
            $data = TPA_API::fetch_azure_data($widget_config);
        } elseif ($widget_type === 'databricks') {
            $data = TPA_API::fetch_databricks_data($widget_config);
        }

        wp_send_json_success($data);
    }

    /**
     * AJAX handler for loading Databricks file
     */
    public function ajax_load_databricks_file() {
        $this->add_diagnostic_log('AJAX: ajax_load_databricks_file called');

        try {
            $this->add_diagnostic_log('AJAX: Verifying nonce...');
            // Verify authentication and permissions
            TPA_Auth::verify_ajax_nonce();
            $this->add_diagnostic_log('AJAX: Nonce verified');

            $file_path = isset($_POST['file_path']) ? sanitize_text_field($_POST['file_path']) : '';
            $this->add_diagnostic_log('AJAX: File path: ' . $file_path);

            if (empty($file_path)) {
                $this->add_diagnostic_log('AJAX: File path is empty');
                wp_send_json_error(array('message' => 'File path is required'));
                return;
            }

            // Log the request for debugging
            $this->add_diagnostic_log('AJAX: Loading Databricks file via API...');
            error_log('TPA: Loading Databricks file: ' . $file_path);

            // Read the file from Databricks
            $data = TPA_API::read_databricks_file($file_path);

            if (is_wp_error($data)) {
                $error_msg = $data->get_error_message();
                $this->add_diagnostic_log('AJAX: API returned error: ' . $error_msg);
                error_log('TPA: Databricks file error: ' . $error_msg);
                wp_send_json_error(array('message' => $error_msg));
                return;
            }

            $this->add_diagnostic_log('AJAX: Data received from API, checking type...');
            $item_count = is_array($data) ? count($data) : 0;
            $this->add_diagnostic_log('AJAX: Successfully loaded file with ' . $item_count . ' items');

            // Check memory usage
            $memory_used = memory_get_usage(true);
            $memory_limit = ini_get('memory_limit');
            $this->add_diagnostic_log('AJAX: Memory usage: ' . round($memory_used / 1024 / 1024, 2) . 'MB / Limit: ' . $memory_limit);

            // Try to encode the data to check if it's valid JSON
            $this->add_diagnostic_log('AJAX: Attempting to encode data as JSON...');
            $json_encoded = json_encode($data);
            if ($json_encoded === false) {
                $json_error = json_last_error_msg();
                $this->add_diagnostic_log('AJAX: JSON encode failed: ' . $json_error);
                wp_send_json_error(array('message' => 'Failed to encode data: ' . $json_error));
                return;
            }

            $json_size = strlen($json_encoded);
            $this->add_diagnostic_log('AJAX: JSON encoded successfully, size: ' . round($json_size / 1024 / 1024, 2) . 'MB');

            // Free memory
            unset($json_encoded);

            $this->add_diagnostic_log('AJAX: Sending success response...');
            error_log('TPA: Successfully loaded Databricks file');

            // Set a higher memory limit temporarily if needed
            @ini_set('memory_limit', '256M');

            wp_send_json_success($data);

            $this->add_diagnostic_log('AJAX: Response sent successfully');

        } catch (Exception $e) {
            $this->add_diagnostic_log('AJAX: Exception: ' . $e->getMessage());
            error_log('TPA: Exception in ajax_load_databricks_file: ' . $e->getMessage());
            wp_send_json_error(array('message' => 'Server error: ' . $e->getMessage()));
        }
    }

    /**
     * Helper: Add diagnostic log entry
     */
    private function add_diagnostic_log($message) {
        $log = get_option('tpa_diagnostic_log', array());
        $log[] = array(
            'time' => current_time('mysql'),
            'message' => $message
        );
        if (count($log) > 100) {
            $log = array_slice($log, -100);
        }
        update_option('tpa_diagnostic_log', $log, false);
    }
}
