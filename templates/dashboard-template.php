<?php
/**
 * Template for displaying dashboard pages
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

get_header();
?>

<div class="tpa-dashboard-wrapper">
    <?php while (have_posts()) : the_post(); ?>

        <article id="post-<?php the_ID(); ?>" <?php post_class('tpa-dashboard'); ?>>

            <header class="tpa-dashboard-header">
                <h1 class="tpa-dashboard-title"><?php the_title(); ?></h1>

                <div class="tpa-dashboard-actions">
                    <button type="button" class="tpa-refresh-button" data-dashboard-id="<?php the_ID(); ?>">
                        <span class="dashicons dashicons-update"></span>
                        <?php _e('Refresh', 'trimontium-website-login'); ?>
                    </button>

                    <?php
                    $refresh_interval = get_post_meta(get_the_ID(), '_tpa_refresh_interval', true);
                    if ($refresh_interval > 0) :
                    ?>
                        <span class="tpa-auto-refresh-indicator">
                            <?php printf(__('Auto-refresh: %ds', 'trimontium-website-login'), $refresh_interval); ?>
                        </span>
                    <?php endif; ?>
                </div>
            </header>

            <div class="tpa-dashboard-content">
                <?php the_content(); ?>
            </div>

            <div class="tpa-dashboard-loading" style="display: none;">
                <div class="spinner"></div>
                <p><?php _e('Loading dashboard data...', 'trimontium-website-login'); ?></p>
            </div>

        </article>

        <?php
        // Initialize auto-refresh if configured
        if ($refresh_interval > 0) :
        ?>
        <script>
        (function($) {
            var refreshInterval = <?php echo intval($refresh_interval); ?> * 1000;
            var dashboardId = <?php the_ID(); ?>;

            setInterval(function() {
                $('.tpa-refresh-button').trigger('click');
            }, refreshInterval);
        })(jQuery);
        </script>
        <?php endif; ?>

    <?php endwhile; ?>
</div>

<?php get_footer(); ?>
