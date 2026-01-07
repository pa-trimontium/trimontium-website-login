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
                        <?php _e('Refresh', 'trimontium-wp-private-dashboards'); ?>
                    </button>
                </div>
            </header>

            <div class="tpa-dashboard-content">
                <?php the_content(); ?>
            </div>

            <div class="tpa-dashboard-loading" style="display: none;">
                <div class="spinner"></div>
                <p><?php _e('Loading dashboard data...', 'trimontium-wp-private-dashboards'); ?></p>
            </div>

        </article>

    <?php endwhile; ?>
</div>

<?php get_footer(); ?>
