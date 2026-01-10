<?php
/**
 * API Integration Class
 * Handles API calls to Azure and Databricks
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class TPA_API {

    /**
     * Singleton instance
     */
    private static $instance = null;

    /**
     * Cache expiration time (in seconds)
     */
    const CACHE_EXPIRATION = 30; // 30 seconds

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
        // Initialize if needed
    }

    /**
     * Get Azure credentials from settings
     */
    private static function get_azure_credentials() {
        return array(
            'tenant_id' => get_option('tpa_azure_tenant_id', ''),
            'client_id' => get_option('tpa_azure_client_id', ''),
            'client_secret' => get_option('tpa_azure_client_secret', ''),
            'subscription_id' => get_option('tpa_azure_subscription_id', '')
        );
    }

    /**
     * Get Databricks credentials from settings
     */
    private static function get_databricks_credentials() {
        return array(
            'workspace_url' => get_option('tpa_databricks_workspace_url', ''),
            'token' => get_option('tpa_databricks_token', '')
        );
    }

    /**
     * Get Azure access token
     */
    private static function get_azure_access_token() {
        $cache_key = 'tpa_azure_token';
        $cached_token = get_transient($cache_key);

        if ($cached_token) {
            return $cached_token;
        }

        $creds = self::get_azure_credentials();

        if (empty($creds['tenant_id']) || empty($creds['client_id']) || empty($creds['client_secret'])) {
            return new WP_Error('missing_credentials', 'Azure credentials not configured');
        }

        $token_url = "https://login.microsoftonline.com/{$creds['tenant_id']}/oauth2/v2.0/token";

        $response = wp_remote_post($token_url, array(
            'body' => array(
                'client_id' => $creds['client_id'],
                'client_secret' => $creds['client_secret'],
                'scope' => 'https://management.azure.com/.default',
                'grant_type' => 'client_credentials'
            ),
            'timeout' => 30
        ));

        if (is_wp_error($response)) {
            return $response;
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);

        if (isset($body['access_token'])) {
            // Cache token for 50 minutes (tokens typically valid for 1 hour)
            set_transient($cache_key, $body['access_token'], 50 * MINUTE_IN_SECONDS);
            return $body['access_token'];
        }

        return new WP_Error('token_error', 'Failed to obtain Azure access token');
    }

    /**
     * Make Azure API request
     */
    public static function azure_api_request($endpoint, $method = 'GET', $body = null) {
        $token = self::get_azure_access_token();

        if (is_wp_error($token)) {
            return $token;
        }

        $url = 'https://management.azure.com' . $endpoint;

        $args = array(
            'method' => $method,
            'headers' => array(
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => 'application/json'
            ),
            'timeout' => 30
        );

        if ($body) {
            $args['body'] = json_encode($body);
        }

        $response = wp_remote_request($url, $args);

        if (is_wp_error($response)) {
            self::log_api_call($endpoint, $method, $body, $response->get_error_message(), 0);
            return $response;
        }

        $status_code = wp_remote_retrieve_response_code($response);
        $response_body = wp_remote_retrieve_body($response);
        $data = json_decode($response_body, true);

        self::log_api_call($endpoint, $method, $body, $data, $status_code);

        if ($status_code >= 400) {
            return new WP_Error('api_error', 'Azure API error: ' . $status_code, $data);
        }

        return $data;
    }

    /**
     * Make Databricks API request
     */
    public static function databricks_api_request($endpoint, $method = 'GET', $body = null) {
        $creds = self::get_databricks_credentials();

        if (empty($creds['workspace_url']) || empty($creds['token'])) {
            return new WP_Error('missing_credentials', 'Databricks credentials not configured');
        }

        $url = rtrim($creds['workspace_url'], '/') . '/api/2.0' . $endpoint;

        $args = array(
            'method' => $method,
            'headers' => array(
                'Authorization' => 'Bearer ' . $creds['token'],
                'Content-Type' => 'application/json'
            ),
            'timeout' => 30
        );

        if ($body) {
            $args['body'] = json_encode($body);
        }

        $response = wp_remote_request($url, $args);

        if (is_wp_error($response)) {
            self::log_api_call($endpoint, $method, $body, $response->get_error_message(), 0);
            return $response;
        }

        $status_code = wp_remote_retrieve_response_code($response);
        $response_body = wp_remote_retrieve_body($response);
        $data = json_decode($response_body, true);

        self::log_api_call($endpoint, $method, $body, $data, $status_code);

        if ($status_code >= 400) {
            return new WP_Error('api_error', 'Databricks API error: ' . $status_code, $data);
        }

        return $data;
    }

    /**
     * Fetch Azure metrics data
     */
    public static function fetch_azure_data($config) {
        $resource_id = $config['resource_id'] ?? '';
        $metric = $config['metric'] ?? '';

        if (empty($resource_id)) {
            return new WP_Error('invalid_config', 'Resource ID is required');
        }

        $cache_key = 'tpa_azure_' . md5($resource_id . $metric);
        $cached_data = get_transient($cache_key);

        if ($cached_data !== false) {
            return $cached_data;
        }

        // Build metrics API endpoint
        $endpoint = $resource_id . '/providers/Microsoft.Insights/metrics';
        $endpoint .= '?api-version=2021-05-01';

        if ($metric) {
            $endpoint .= '&metricnames=' . urlencode($metric);
        }

        $data = self::azure_api_request($endpoint);

        if (!is_wp_error($data)) {
            set_transient($cache_key, $data, self::CACHE_EXPIRATION);
        }

        return $data;
    }

    /**
     * Fetch Databricks jobs data
     */
    public static function fetch_databricks_data($config) {
        $type = $config['type'] ?? 'jobs';
        $limit = $config['limit'] ?? 25;

        $cache_key = 'tpa_databricks_' . $type . '_' . $limit;
        $cached_data = get_transient($cache_key);

        if ($cached_data !== false) {
            return $cached_data;
        }

        $endpoint = '';
        $body = null;

        switch ($type) {
            case 'jobs':
                $endpoint = '/jobs/list';
                $body = array('limit' => intval($limit));
                break;

            case 'clusters':
                $endpoint = '/clusters/list';
                break;

            case 'runs':
                $endpoint = '/jobs/runs/list';
                $body = array('limit' => intval($limit));
                break;

            default:
                return new WP_Error('invalid_type', 'Invalid Databricks data type');
        }

        $method = $body ? 'POST' : 'GET';
        $data = self::databricks_api_request($endpoint, $method, $body);

        if (!is_wp_error($data)) {
            set_transient($cache_key, $data, self::CACHE_EXPIRATION);
        }

        return $data;
    }

    /**
     * Get Azure data for dashboard
     */
    public static function get_azure_data($dashboard_id) {
        $config = get_post_meta($dashboard_id, '_tpa_azure_config', true);

        if (!$config) {
            return array('error' => 'Dashboard not configured');
        }

        return self::fetch_azure_data($config);
    }

    /**
     * Get Databricks data for dashboard
     */
    public static function get_databricks_data($dashboard_id) {
        $config = get_post_meta($dashboard_id, '_tpa_databricks_config', true);

        if (!$config) {
            return array('error' => 'Dashboard not configured');
        }

        return self::fetch_databricks_data($config);
    }

    /**
     * Helper: Add diagnostic log entry
     */
    private static function add_diagnostic_log($message) {
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

    /**
     * Read file from Databricks Volume
     */
    public static function read_databricks_file($file_path, $force_refresh = false) {
        self::add_diagnostic_log('API: Starting read_databricks_file');
        self::add_diagnostic_log('API: Force refresh: ' . ($force_refresh ? 'yes' : 'no'));

        if (empty($file_path)) {
            self::add_diagnostic_log('API: Empty file path provided');
            error_log('TPA API: Empty file path provided');
            return new WP_Error('invalid_path', 'File path is required');
        }

        self::add_diagnostic_log('API: File path: ' . $file_path);
        error_log('TPA API: Reading Databricks file: ' . $file_path);

        $cache_key = 'tpa_dbx_file_' . md5($file_path);

        // Skip cache if force refresh is requested
        if (!$force_refresh) {
            $cached_data = get_transient($cache_key);

            if ($cached_data !== false) {
                self::add_diagnostic_log('API: Returning cached data');
                error_log('TPA API: Returning cached data');
                return $cached_data;
            }
        } else {
            self::add_diagnostic_log('API: Skipping cache due to force refresh');
            error_log('TPA API: Skipping cache due to force refresh');
        }

        self::add_diagnostic_log('API: No cache, making fresh request');

        // Use Databricks Files API to read the file
        // For Unity Catalog Volumes, we need to use the Files API
        $endpoint = '/api/2.0/fs/files' . $file_path;
        self::add_diagnostic_log('API: Endpoint: ' . $endpoint);
        error_log('TPA API: Databricks endpoint: ' . $endpoint);

        $result = self::databricks_file_request($endpoint);

        if (is_wp_error($result)) {
            $error_msg = $result->get_error_message();
            self::add_diagnostic_log('API: Request failed: ' . $error_msg);
            error_log('TPA API: Databricks file request failed: ' . $error_msg);
            return $result;
        }

        self::add_diagnostic_log('API: Request succeeded, content length: ' . strlen($result));
        error_log('TPA API: File content length: ' . strlen($result));

        // Try to decode as JSON
        $decoded = json_decode($result, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            $data = $decoded;
            self::add_diagnostic_log('API: Successfully decoded JSON with ' . count($data) . ' items');
            error_log('TPA API: Successfully decoded JSON data');
        } else {
            $data = array('content' => $result);
            self::add_diagnostic_log('API: Not JSON, returning raw content');
            error_log('TPA API: File is not JSON, returning as raw content. Error: ' . json_last_error_msg());
        }

        set_transient($cache_key, $data, self::CACHE_EXPIRATION);
        self::add_diagnostic_log('API: Data cached and returning');

        return $data;
    }

    /**
     * Make Databricks file download request
     */
    private static function databricks_file_request($endpoint) {
        self::add_diagnostic_log('API: Starting databricks_file_request');

        $creds = self::get_databricks_credentials();

        if (empty($creds['workspace_url']) || empty($creds['token'])) {
            self::add_diagnostic_log('API: Credentials missing');
            error_log('TPA API: Databricks credentials missing');
            return new WP_Error('missing_credentials', 'Databricks credentials not configured');
        }

        $url = rtrim($creds['workspace_url'], '') . $endpoint;
        self::add_diagnostic_log('API: Full URL: ' . $url);
        self::add_diagnostic_log('API: Token present: ' . (strlen($creds['token']) > 0 ? 'yes' : 'no'));
        error_log('TPA API: Full Databricks URL: ' . $url);
        error_log('TPA API: Using token: ' . substr($creds['token'], 0, 10) . '...');

        $args = array(
            'method' => 'GET',
            'headers' => array(
                'Authorization' => 'Bearer ' . $creds['token']
            ),
            'timeout' => 30
        );

        self::add_diagnostic_log('API: Making HTTP request to Databricks...');
        error_log('TPA API: Making request to Databricks...');
        $response = wp_remote_request($url, $args);

        if (is_wp_error($response)) {
            $error_msg = $response->get_error_message();
            self::add_diagnostic_log('API: wp_remote_request error: ' . $error_msg);
            error_log('TPA API: WP Error: ' . $error_msg);
            self::log_api_call($endpoint, 'GET', null, $error_msg, 0);
            return $response;
        }

        $status_code = wp_remote_retrieve_response_code($response);
        $response_body = wp_remote_retrieve_body($response);

        self::add_diagnostic_log('API: HTTP status code: ' . $status_code);
        self::add_diagnostic_log('API: Response body length: ' . strlen($response_body));
        error_log('TPA API: Response status code: ' . $status_code);
        error_log('TPA API: Response body length: ' . strlen($response_body));

        self::log_api_call($endpoint, 'GET', null, substr($response_body, 0, 1000), $status_code);

        if ($status_code >= 400) {
            $error_detail = substr($response_body, 0, 200);
            self::add_diagnostic_log('API: HTTP error ' . $status_code . ': ' . $error_detail);
            error_log('TPA API: HTTP error ' . $status_code . ': ' . $error_detail);
            return new WP_Error('api_error', 'Databricks file read error: ' . $status_code . ' - ' . $error_detail);
        }

        self::add_diagnostic_log('API: Request completed successfully');
        return $response_body;
    }

    /**
     * Log API call to database
     */
    private static function log_api_call($endpoint, $method, $request_data, $response_data, $status_code) {
        try {
            global $wpdb;

            $table_name = $wpdb->prefix . 'tpa_api_logs';

            // Check if table exists before trying to insert
            $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") === $table_name;
            if (!$table_exists) {
                error_log('TPA API: Log table does not exist, skipping logging');
                return;
            }

            $result = $wpdb->insert(
                $table_name,
                array(
                    'user_id' => get_current_user_id(),
                    'api_endpoint' => $endpoint,
                    'request_data' => json_encode($request_data),
                    'response_data' => is_string($response_data) ? $response_data : json_encode($response_data),
                    'status_code' => $status_code
                ),
                array('%d', '%s', '%s', '%s', '%d')
            );

            if ($result === false) {
                error_log('TPA API: Failed to insert log: ' . $wpdb->last_error);
            }

            // Clean up old logs (keep last 1000 entries)
            $wpdb->query(
                "DELETE FROM $table_name WHERE id NOT IN (
                    SELECT id FROM (
                        SELECT id FROM $table_name ORDER BY created_at DESC LIMIT 1000
                    ) tmp
                )"
            );
        } catch (Exception $e) {
            error_log('TPA API: Exception in log_api_call: ' . $e->getMessage());
            // Don't let logging errors break the actual functionality
        }
    }

    /**
     * Clear API cache
     */
    public static function clear_cache($type = 'all') {
        global $wpdb;

        if ($type === 'azure' || $type === 'all') {
            $wpdb->query(
                "DELETE FROM {$wpdb->options}
                WHERE option_name LIKE '_transient_tpa_azure_%'
                OR option_name LIKE '_transient_timeout_tpa_azure_%'"
            );
        }

        if ($type === 'databricks' || $type === 'all') {
            $wpdb->query(
                "DELETE FROM {$wpdb->options}
                WHERE option_name LIKE '_transient_tpa_databricks_%'
                OR option_name LIKE '_transient_timeout_tpa_databricks_%'"
            );
        }

        if ($type === 'all') {
            delete_transient('tpa_azure_token');
        }
    }

    /**
     * Test Azure connection
     */
    public static function test_azure_connection() {
        $token = self::get_azure_access_token();

        if (is_wp_error($token)) {
            return array(
                'success' => false,
                'message' => $token->get_error_message()
            );
        }

        $creds = self::get_azure_credentials();
        $endpoint = "/subscriptions/{$creds['subscription_id']}?api-version=2020-01-01";
        $result = self::azure_api_request($endpoint);

        if (is_wp_error($result)) {
            return array(
                'success' => false,
                'message' => $result->get_error_message()
            );
        }

        return array(
            'success' => true,
            'message' => 'Successfully connected to Azure',
            'subscription' => $result['displayName'] ?? 'Unknown'
        );
    }

    /**
     * Test Databricks connection
     */
    public static function test_databricks_connection() {
        $result = self::databricks_api_request('/clusters/list');

        if (is_wp_error($result)) {
            return array(
                'success' => false,
                'message' => $result->get_error_message()
            );
        }

        return array(
            'success' => true,
            'message' => 'Successfully connected to Databricks',
            'clusters' => count($result['clusters'] ?? array())
        );
    }
}
