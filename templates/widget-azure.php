<?php
/**
 * Template for Azure widget
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

$widget_id = 'azure-widget-' . uniqid();
$widget_type = $atts['type'];
$resource_id = $atts['resource_id'];
$metric = $atts['metric'];
$title = $atts['title'] ?: __('Azure Metrics', 'trimontium-website-login');
$height = $atts['height'];
?>

<div class="tpa-widget tpa-azure-widget" id="<?php echo esc_attr($widget_id); ?>" style="height: <?php echo esc_attr($height); ?>;">
    <div class="tpa-widget-header">
        <h3 class="tpa-widget-title"><?php echo esc_html($title); ?></h3>
        <button type="button" class="tpa-widget-refresh" data-widget-id="<?php echo esc_attr($widget_id); ?>">
            <span class="dashicons dashicons-update"></span>
        </button>
    </div>

    <div class="tpa-widget-body">
        <div class="tpa-widget-loading">
            <span class="spinner is-active"></span>
            <p><?php _e('Loading Azure data...', 'trimontium-website-login'); ?></p>
        </div>

        <div class="tpa-widget-content" style="display: none;">
            <canvas id="<?php echo esc_attr($widget_id); ?>-chart"></canvas>
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
            resource_id: '<?php echo esc_js($resource_id); ?>',
            metric: '<?php echo esc_js($metric); ?>'
        };

        // Load widget data
        function loadAzureWidget() {
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
                    widget_type: 'azure',
                    widget_config: config
                },
                success: function(response) {
                    if (response.success) {
                        renderAzureData($widget, response.data);
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

        function renderAzureData($widget, data) {
            // Render Azure metrics data
            // This is a simplified example - you would implement proper chart rendering
            var $content = $widget.find('.tpa-widget-content');
            $content.html('<pre>' + JSON.stringify(data, null, 2) + '</pre>');

            // You can use Chart.js or another charting library here
            // Example: new Chart(ctx, {...});
        }

        function showError($widget, message) {
            $widget.find('.tpa-widget-loading').hide();
            $widget.find('.tpa-widget-error').show();
            $widget.find('.error-message').text(message);
        }

        // Initial load
        loadAzureWidget();

        // Refresh button
        $widget.find('.tpa-widget-refresh').on('click', function() {
            loadAzureWidget();
        });
    });
})(jQuery);
</script>
