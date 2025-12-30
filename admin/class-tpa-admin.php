<?php
/**
 * Admin Interface Class
 * Handles admin pages and settings
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class TPA_Admin {

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
        // Add admin menu
        add_action('admin_menu', array($this, 'add_admin_menu'));

        // Register settings
        add_action('admin_init', array($this, 'register_settings'));

        // Add AJAX handlers
        add_action('wp_ajax_tpa_test_azure_connection', array($this, 'ajax_test_azure_connection'));
        add_action('wp_ajax_tpa_test_databricks_connection', array($this, 'ajax_test_databricks_connection'));
        add_action('wp_ajax_tpa_clear_cache', array($this, 'ajax_clear_cache'));

        // Add user role column
        add_filter('manage_users_columns', array($this, 'add_user_columns'));
        add_filter('manage_users_custom_column', array($this, 'render_user_column'), 10, 3);

        // Add bulk actions
        add_filter('bulk_actions-users', array($this, 'add_bulk_actions'));
        add_filter('handle_bulk_actions-users', array($this, 'handle_bulk_actions'), 10, 3);
    }

    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_menu_page(
            __('Private Area', 'trimontium-website-login'),
            __('Private Area', 'trimontium-website-login'),
            'manage_options',
            'trimontium-website-login',
            array($this, 'render_main_page'),
            'dashicons-lock',
            30
        );

        add_submenu_page(
            'trimontium-website-login',
            __('Settings', 'trimontium-website-login'),
            __('Settings', 'trimontium-website-login'),
            'manage_options',
            'trimontium-website-login-settings',
            array($this, 'render_settings_page')
        );

        add_submenu_page(
            'trimontium-website-login',
            __('API Credentials', 'trimontium-website-login'),
            __('API Credentials', 'trimontium-website-login'),
            'manage_options',
            'trimontium-website-login-api',
            array($this, 'render_api_page')
        );

        add_submenu_page(
            'trimontium-website-login',
            __('Users', 'trimontium-website-login'),
            __('Users', 'trimontium-website-login'),
            'manage_options',
            'trimontium-website-login-users',
            array($this, 'render_users_page')
        );

        add_submenu_page(
            'trimontium-website-login',
            __('API Logs', 'trimontium-website-login'),
            __('API Logs', 'trimontium-website-login'),
            'manage_options',
            'trimontium-website-login-logs',
            array($this, 'render_logs_page')
        );
    }

    /**
     * Register settings
     */
    public function register_settings() {
        // Azure settings
        register_setting('tpa_azure_settings', 'tpa_azure_tenant_id');
        register_setting('tpa_azure_settings', 'tpa_azure_client_id');
        register_setting('tpa_azure_settings', 'tpa_azure_client_secret');
        register_setting('tpa_azure_settings', 'tpa_azure_subscription_id');

        // Databricks settings
        register_setting('tpa_databricks_settings', 'tpa_databricks_workspace_url');
        register_setting('tpa_databricks_settings', 'tpa_databricks_token');

        // General settings
        register_setting('tpa_general_settings', 'tpa_main_dashboard_page');
        register_setting('tpa_general_settings', 'tpa_enable_logging');
        register_setting('tpa_general_settings', 'tpa_cache_duration');
    }

    /**
     * Render main admin page
     */
    public function render_main_page() {
        ?>
        <div class="wrap">
            <h1><?php _e('Trimontium Private Area', 'trimontium-website-login'); ?></h1>

            <div class="tpa-admin-grid">
                <div class="tpa-admin-card">
                    <h2><?php _e('Quick Stats', 'trimontium-website-login'); ?></h2>
                    <?php
                    $users = TPA_Roles::get_private_area_users();
                    $dashboards = wp_count_posts('tpa_dashboard');
                    ?>
                    <ul>
                        <li><?php printf(__('Authorized Users: %d', 'trimontium-website-login'), count($users)); ?></li>
                        <li><?php printf(__('Dashboards: %d', 'trimontium-website-login'), $dashboards->publish); ?></li>
                    </ul>
                </div>

                <div class="tpa-admin-card">
                    <h2><?php _e('Quick Actions', 'trimontium-website-login'); ?></h2>
                    <p>
                        <a href="<?php echo admin_url('post-new.php?post_type=tpa_dashboard'); ?>" class="button button-primary">
                            <?php _e('Create New Dashboard', 'trimontium-website-login'); ?>
                        </a>
                    </p>
                    <p>
                        <a href="<?php echo admin_url('admin.php?page=trimontium-website-login-users'); ?>" class="button">
                            <?php _e('Manage Users', 'trimontium-website-login'); ?>
                        </a>
                    </p>
                    <p>
                        <a href="<?php echo admin_url('admin.php?page=trimontium-website-login-api'); ?>" class="button">
                            <?php _e('Configure API Credentials', 'trimontium-website-login'); ?>
                        </a>
                    </p>
                </div>

                <div class="tpa-admin-card">
                    <h2><?php _e('Connection Status', 'trimontium-website-login'); ?></h2>
                    <div id="tpa-connection-status">
                        <p><?php _e('Click to test connections:', 'trimontium-website-login'); ?></p>
                        <p>
                            <button type="button" class="button" id="test-azure-connection">
                                <?php _e('Test Azure Connection', 'trimontium-website-login'); ?>
                            </button>
                        </p>
                        <p>
                            <button type="button" class="button" id="test-databricks-connection">
                                <?php _e('Test Databricks Connection', 'trimontium-website-login'); ?>
                            </button>
                        </p>
                        <div id="connection-results"></div>
                    </div>
                </div>
            </div>
        </div>

        <style>
            .tpa-admin-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
                gap: 20px;
                margin-top: 20px;
            }
            .tpa-admin-card {
                background: #fff;
                border: 1px solid #ccd0d4;
                padding: 20px;
                box-shadow: 0 1px 1px rgba(0,0,0,.04);
            }
            .tpa-admin-card h2 {
                margin-top: 0;
            }
        </style>
        <?php
    }

    /**
     * Render settings page
     */
    public function render_settings_page() {
        ?>
        <div class="wrap">
            <h1><?php _e('Private Area Settings', 'trimontium-website-login'); ?></h1>

            <form method="post" action="options.php">
                <?php
                settings_fields('tpa_general_settings');
                ?>

                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="tpa_main_dashboard_page">
                                <?php _e('Main Dashboard Page', 'trimontium-website-login'); ?>
                            </label>
                        </th>
                        <td>
                            <?php
                            wp_dropdown_pages(array(
                                'name' => 'tpa_main_dashboard_page',
                                'selected' => get_option('tpa_main_dashboard_page'),
                                'show_option_none' => __('Select a page', 'trimontium-website-login')
                            ));
                            ?>
                            <p class="description">
                                <?php _e('Users will be redirected to this page after login', 'trimontium-website-login'); ?>
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">
                            <label for="tpa_enable_logging">
                                <?php _e('Enable API Logging', 'trimontium-website-login'); ?>
                            </label>
                        </th>
                        <td>
                            <input type="checkbox" name="tpa_enable_logging" id="tpa_enable_logging"
                                   value="1" <?php checked(get_option('tpa_enable_logging'), '1'); ?>>
                            <p class="description">
                                <?php _e('Log all API requests for debugging', 'trimontium-website-login'); ?>
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">
                            <label for="tpa_cache_duration">
                                <?php _e('Cache Duration (seconds)', 'trimontium-website-login'); ?>
                            </label>
                        </th>
                        <td>
                            <input type="number" name="tpa_cache_duration" id="tpa_cache_duration"
                                   value="<?php echo esc_attr(get_option('tpa_cache_duration', '300')); ?>"
                                   min="0" step="1" class="regular-text">
                            <p class="description">
                                <?php _e('How long to cache API responses', 'trimontium-website-login'); ?>
                            </p>
                        </td>
                    </tr>
                </table>

                <?php submit_button(); ?>
            </form>

            <hr>

            <h2><?php _e('Cache Management', 'trimontium-website-login'); ?></h2>
            <p>
                <button type="button" class="button" id="clear-all-cache">
                    <?php _e('Clear All Cache', 'trimontium-website-login'); ?>
                </button>
                <button type="button" class="button" id="clear-azure-cache">
                    <?php _e('Clear Azure Cache', 'trimontium-website-login'); ?>
                </button>
                <button type="button" class="button" id="clear-databricks-cache">
                    <?php _e('Clear Databricks Cache', 'trimontium-website-login'); ?>
                </button>
            </p>
            <div id="cache-clear-results"></div>
        </div>
        <?php
    }

    /**
     * Render API credentials page
     */
    public function render_api_page() {
        ?>
        <div class="wrap">
            <h1><?php _e('API Credentials', 'trimontium-website-login'); ?></h1>

            <h2 class="nav-tab-wrapper">
                <a href="#azure-tab" class="nav-tab nav-tab-active"><?php _e('Azure', 'trimontium-website-login'); ?></a>
                <a href="#databricks-tab" class="nav-tab"><?php _e('Databricks', 'trimontium-website-login'); ?></a>
            </h2>

            <div id="azure-tab" class="tab-content">
                <form method="post" action="options.php">
                    <?php settings_fields('tpa_azure_settings'); ?>

                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="tpa_azure_tenant_id"><?php _e('Tenant ID', 'trimontium-website-login'); ?></label>
                            </th>
                            <td>
                                <input type="text" name="tpa_azure_tenant_id" id="tpa_azure_tenant_id"
                                       value="<?php echo esc_attr(get_option('tpa_azure_tenant_id')); ?>"
                                       class="regular-text">
                            </td>
                        </tr>

                        <tr>
                            <th scope="row">
                                <label for="tpa_azure_client_id"><?php _e('Client ID', 'trimontium-website-login'); ?></label>
                            </th>
                            <td>
                                <input type="text" name="tpa_azure_client_id" id="tpa_azure_client_id"
                                       value="<?php echo esc_attr(get_option('tpa_azure_client_id')); ?>"
                                       class="regular-text">
                            </td>
                        </tr>

                        <tr>
                            <th scope="row">
                                <label for="tpa_azure_client_secret"><?php _e('Client Secret', 'trimontium-website-login'); ?></label>
                            </th>
                            <td>
                                <input type="password" name="tpa_azure_client_secret" id="tpa_azure_client_secret"
                                       value="<?php echo esc_attr(get_option('tpa_azure_client_secret')); ?>"
                                       class="regular-text">
                            </td>
                        </tr>

                        <tr>
                            <th scope="row">
                                <label for="tpa_azure_subscription_id"><?php _e('Subscription ID', 'trimontium-website-login'); ?></label>
                            </th>
                            <td>
                                <input type="text" name="tpa_azure_subscription_id" id="tpa_azure_subscription_id"
                                       value="<?php echo esc_attr(get_option('tpa_azure_subscription_id')); ?>"
                                       class="regular-text">
                            </td>
                        </tr>
                    </table>

                    <?php submit_button(); ?>
                </form>
            </div>

            <div id="databricks-tab" class="tab-content" style="display: none;">
                <form method="post" action="options.php">
                    <?php settings_fields('tpa_databricks_settings'); ?>

                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="tpa_databricks_workspace_url"><?php _e('Workspace URL', 'trimontium-website-login'); ?></label>
                            </th>
                            <td>
                                <input type="url" name="tpa_databricks_workspace_url" id="tpa_databricks_workspace_url"
                                       value="<?php echo esc_attr(get_option('tpa_databricks_workspace_url')); ?>"
                                       class="regular-text" placeholder="https://adb-xxxxx.azuredatabricks.net">
                            </td>
                        </tr>

                        <tr>
                            <th scope="row">
                                <label for="tpa_databricks_token"><?php _e('Access Token', 'trimontium-website-login'); ?></label>
                            </th>
                            <td>
                                <input type="password" name="tpa_databricks_token" id="tpa_databricks_token"
                                       value="<?php echo esc_attr(get_option('tpa_databricks_token')); ?>"
                                       class="regular-text">
                            </td>
                        </tr>
                    </table>

                    <?php submit_button(); ?>
                </form>
            </div>
        </div>

        <script>
        jQuery(document).ready(function($) {
            $('.nav-tab').click(function(e) {
                e.preventDefault();
                $('.nav-tab').removeClass('nav-tab-active');
                $(this).addClass('nav-tab-active');
                $('.tab-content').hide();
                $($(this).attr('href')).show();
            });
        });
        </script>
        <?php
    }

    /**
     * Render users management page
     */
    public function render_users_page() {
        $users = TPA_Roles::get_private_area_users();
        ?>
        <div class="wrap">
            <h1><?php _e('Private Area Users', 'trimontium-website-login'); ?></h1>

            <p><?php _e('Users with access to the private area:', 'trimontium-website-login'); ?></p>

            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php _e('User', 'trimontium-website-login'); ?></th>
                        <th><?php _e('Email', 'trimontium-website-login'); ?></th>
                        <th><?php _e('Role', 'trimontium-website-login'); ?></th>
                        <th><?php _e('Azure Access', 'trimontium-website-login'); ?></th>
                        <th><?php _e('Databricks Access', 'trimontium-website-login'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user) : ?>
                        <tr>
                            <td>
                                <strong>
                                    <a href="<?php echo get_edit_user_link($user->ID); ?>">
                                        <?php echo esc_html($user->display_name); ?>
                                    </a>
                                </strong>
                            </td>
                            <td><?php echo esc_html($user->user_email); ?></td>
                            <td><?php echo esc_html(implode(', ', $user->roles)); ?></td>
                            <td>
                                <?php echo TPA_Roles::user_can_access_azure($user->ID) ? '✓' : '—'; ?>
                            </td>
                            <td>
                                <?php echo TPA_Roles::user_can_access_databricks($user->ID) ? '✓' : '—'; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <p>
                <a href="<?php echo admin_url('users.php'); ?>" class="button">
                    <?php _e('Manage All Users', 'trimontium-website-login'); ?>
                </a>
            </p>
        </div>
        <?php
    }

    /**
     * Render API logs page
     */
    public function render_logs_page() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'tpa_api_logs';

        $logs = $wpdb->get_results(
            "SELECT * FROM $table_name ORDER BY created_at DESC LIMIT 100"
        );

        ?>
        <div class="wrap">
            <h1><?php _e('API Logs', 'trimontium-website-login'); ?></h1>

            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php _e('Time', 'trimontium-website-login'); ?></th>
                        <th><?php _e('User', 'trimontium-website-login'); ?></th>
                        <th><?php _e('Endpoint', 'trimontium-website-login'); ?></th>
                        <th><?php _e('Status', 'trimontium-website-login'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($logs)) : ?>
                        <tr>
                            <td colspan="4"><?php _e('No logs found', 'trimontium-website-login'); ?></td>
                        </tr>
                    <?php else : ?>
                        <?php foreach ($logs as $log) : ?>
                            <tr>
                                <td><?php echo esc_html($log->created_at); ?></td>
                                <td>
                                    <?php
                                    $user = get_user_by('id', $log->user_id);
                                    echo $user ? esc_html($user->display_name) : __('Unknown', 'trimontium-website-login');
                                    ?>
                                </td>
                                <td><?php echo esc_html($log->api_endpoint); ?></td>
                                <td>
                                    <span class="status-<?php echo $log->status_code < 400 ? 'success' : 'error'; ?>">
                                        <?php echo esc_html($log->status_code); ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php
    }

    /**
     * AJAX: Test Azure connection
     */
    public function ajax_test_azure_connection() {
        check_ajax_referer('tpa_ajax_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Permission denied'));
        }

        $result = TPA_API::test_azure_connection();
        wp_send_json($result);
    }

    /**
     * AJAX: Test Databricks connection
     */
    public function ajax_test_databricks_connection() {
        check_ajax_referer('tpa_ajax_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Permission denied'));
        }

        $result = TPA_API::test_databricks_connection();
        wp_send_json($result);
    }

    /**
     * AJAX: Clear cache
     */
    public function ajax_clear_cache() {
        check_ajax_referer('tpa_ajax_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Permission denied'));
        }

        $type = isset($_POST['type']) ? sanitize_text_field($_POST['type']) : 'all';
        TPA_API::clear_cache($type);

        wp_send_json_success(array('message' => 'Cache cleared successfully'));
    }

    /**
     * Add user columns
     */
    public function add_user_columns($columns) {
        $columns['private_area'] = __('Private Area Access', 'trimontium-website-login');
        return $columns;
    }

    /**
     * Render user column
     */
    public function render_user_column($output, $column_name, $user_id) {
        if ($column_name === 'private_area') {
            return TPA_Roles::user_can_access_private_area($user_id) ? '✓' : '—';
        }
        return $output;
    }

    /**
     * Add bulk actions
     */
    public function add_bulk_actions($actions) {
        $actions['grant_private_area_access'] = __('Grant Private Area Access', 'trimontium-website-login');
        $actions['revoke_private_area_access'] = __('Revoke Private Area Access', 'trimontium-website-login');
        return $actions;
    }

    /**
     * Handle bulk actions
     */
    public function handle_bulk_actions($redirect_url, $action, $user_ids) {
        if ($action === 'grant_private_area_access') {
            foreach ($user_ids as $user_id) {
                TPA_Roles::assign_private_area_access($user_id);
            }
            $redirect_url = add_query_arg('private_area_granted', count($user_ids), $redirect_url);
        }

        if ($action === 'revoke_private_area_access') {
            foreach ($user_ids as $user_id) {
                TPA_Roles::remove_private_area_access($user_id);
            }
            $redirect_url = add_query_arg('private_area_revoked', count($user_ids), $redirect_url);
        }

        return $redirect_url;
    }
}
