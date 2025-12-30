<?php
/**
 * Template for Databricks widget
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

$widget_id = 'databricks-widget-' . uniqid();
$widget_type = $atts['type'];
$workspace = $atts['workspace'];
$limit = $atts['limit'];
$title = $atts['title'] ?: __('Databricks ' . ucfirst($widget_type), 'trimontium-website-login');
$height = $atts['height'];
?>

<div class="tpa-widget tpa-databricks-widget" id="<?php echo esc_attr($widget_id); ?>" style="height: <?php echo esc_attr($height); ?>;">
    <div class="tpa-widget-header">
        <h3 class="tpa-widget-title"><?php echo esc_html($title); ?></h3>
        <button type="button" class="tpa-widget-refresh" data-widget-id="<?php echo esc_attr($widget_id); ?>">
            <span class="dashicons dashicons-update"></span>
        </button>
    </div>

    <div class="tpa-widget-body">
        <div class="tpa-widget-loading">
            <span class="spinner is-active"></span>
            <p><?php _e('Loading Databricks data...', 'trimontium-website-login'); ?></p>
        </div>

        <div class="tpa-widget-content" style="display: none;">
            <div class="tpa-databricks-list"></div>
        </div>

        <div class="tpa-widget-error" style="display: none;">
            <p class="error-message"></p>
        </div>
    </div>
</div>

<script>
(function($) {
    $(document).ready(function() {
        var widgetId = '<?php echo esc_js($widget_id); ?>';
        var config = {
            type: '<?php echo esc_js($widget_type); ?>',
            workspace: '<?php echo esc_js($workspace); ?>',
            limit: <?php echo intval($limit); ?>
        };

        // Load widget data
        function loadDatabricksWidget() {
            var $widget = $('#' + widgetId);
            $widget.find('.tpa-widget-loading').show();
            $widget.find('.tpa-widget-content').hide();
            $widget.find('.tpa-widget-error').hide();

            $.ajax({
                url: tpaAjax.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'tpa_refresh_widget',
                    nonce: tpaAjax.nonce,
                    widget_type: 'databricks',
                    widget_config: config
                },
                success: function(response) {
                    if (response.success) {
                        renderDatabricksData($widget, response.data, config.type);
                        $widget.find('.tpa-widget-loading').hide();
                        $widget.find('.tpa-widget-content').show();
                    } else {
                        showError($widget, response.data.message || 'Failed to load data');
                    }
                },
                error: function() {
                    showError($widget, 'Network error occurred');
                }
            });
        }

        function renderDatabricksData($widget, data, type) {
            var $list = $widget.find('.tpa-databricks-list');
            $list.empty();

            if (type === 'jobs' && data.jobs) {
                var html = '<table class="tpa-data-table"><thead><tr><th>Job Name</th><th>Status</th><th>Created</th></tr></thead><tbody>';
                data.jobs.forEach(function(job) {
                    html += '<tr>';
                    html += '<td>' + (job.settings.name || 'N/A') + '</td>';
                    html += '<td>' + (job.state ? job.state.life_cycle_state : 'N/A') + '</td>';
                    html += '<td>' + new Date(job.created_time).toLocaleString() + '</td>';
                    html += '</tr>';
                });
                html += '</tbody></table>';
                $list.html(html);
            } else if (type === 'clusters' && data.clusters) {
                var html = '<table class="tpa-data-table"><thead><tr><th>Cluster Name</th><th>State</th><th>Workers</th></tr></thead><tbody>';
                data.clusters.forEach(function(cluster) {
                    html += '<tr>';
                    html += '<td>' + (cluster.cluster_name || 'N/A') + '</td>';
                    html += '<td>' + (cluster.state || 'N/A') + '</td>';
                    html += '<td>' + (cluster.num_workers || 'N/A') + '</td>';
                    html += '</tr>';
                });
                html += '</tbody></table>';
                $list.html(html);
            } else if (type === 'runs' && data.runs) {
                var html = '<table class="tpa-data-table"><thead><tr><th>Run ID</th><th>Job Name</th><th>State</th><th>Start Time</th></tr></thead><tbody>';
                data.runs.forEach(function(run) {
                    html += '<tr>';
                    html += '<td>' + run.run_id + '</td>';
                    html += '<td>' + (run.run_name || 'N/A') + '</td>';
                    html += '<td>' + (run.state ? run.state.life_cycle_state : 'N/A') + '</td>';
                    html += '<td>' + new Date(run.start_time).toLocaleString() + '</td>';
                    html += '</tr>';
                });
                html += '</tbody></table>';
                $list.html(html);
            } else {
                $list.html('<pre>' + JSON.stringify(data, null, 2) + '</pre>');
            }
        }

        function showError($widget, message) {
            $widget.find('.tpa-widget-loading').hide();
            $widget.find('.tpa-widget-error').show();
            $widget.find('.error-message').text(message);
        }

        // Initial load
        loadDatabricksWidget();

        // Refresh button
        $widget.find('.tpa-widget-refresh').on('click', function() {
            loadDatabricksWidget();
        });
    });
})(jQuery);
</script>
