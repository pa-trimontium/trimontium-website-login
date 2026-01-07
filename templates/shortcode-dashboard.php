<?php
/**
 * Template for dashboard shortcode
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

$dashboard_id = $atts['id'];
$layout = $atts['layout'];
$columns = intval($atts['columns']);
?>

<div class="tpa-shortcode-dashboard" data-dashboard-id="<?php echo esc_attr($dashboard_id); ?>" data-layout="<?php echo esc_attr($layout); ?>">
    <div class="tpa-dashboard-grid" style="grid-template-columns: repeat(<?php echo $columns; ?>, 1fr);">
        <!-- Dashboard widgets will be loaded here via JavaScript -->
        <div class="tpa-dashboard-placeholder">
            <p><?php _e('Loading dashboard...', 'trimontium-wp-private-dashboards'); ?></p>
        </div>
    </div>
</div>
