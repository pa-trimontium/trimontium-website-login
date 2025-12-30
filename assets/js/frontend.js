/**
 * Frontend JavaScript for Trimontium Private Area
 */

(function($) {
    'use strict';

    var TPA = {
        /**
         * Initialize
         */
        init: function() {
            this.bindEvents();
        },

        /**
         * Bind events
         */
        bindEvents: function() {
            // Dashboard refresh button
            $(document).on('click', '.tpa-refresh-button', this.refreshDashboard);

            // Auto-load shortcode dashboards
            $('.tpa-shortcode-dashboard').each(function() {
                TPA.loadShortcodeDashboard($(this));
            });
        },

        /**
         * Refresh dashboard
         */
        refreshDashboard: function(e) {
            e.preventDefault();

            var $button = $(this);
            var dashboardId = $button.data('dashboard-id');
            var $wrapper = $button.closest('.tpa-dashboard-wrapper');

            $button.prop('disabled', true);
            $wrapper.find('.tpa-dashboard-loading').show();

            $.ajax({
                url: tpaAjax.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'tpa_get_dashboard_data',
                    nonce: tpaAjax.nonce,
                    dashboard_id: dashboardId
                },
                success: function(response) {
                    if (response.success) {
                        // Trigger refresh on all widgets
                        $wrapper.find('.tpa-widget-refresh').trigger('click');
                    } else {
                        alert('Failed to refresh dashboard: ' + (response.data.message || 'Unknown error'));
                    }
                },
                error: function() {
                    alert('Network error occurred while refreshing dashboard');
                },
                complete: function() {
                    $button.prop('disabled', false);
                    $wrapper.find('.tpa-dashboard-loading').hide();
                }
            });
        },

        /**
         * Load shortcode dashboard
         */
        loadShortcodeDashboard: function($dashboard) {
            var dashboardId = $dashboard.data('dashboard-id');
            var layout = $dashboard.data('layout');

            // Implementation for loading shortcode dashboard
            // This would fetch and render widgets based on configuration
        },

        /**
         * Format date
         */
        formatDate: function(timestamp) {
            var date = new Date(timestamp);
            return date.toLocaleString();
        },

        /**
         * Format number
         */
        formatNumber: function(num, decimals) {
            decimals = decimals || 2;
            return Number(num).toFixed(decimals);
        },

        /**
         * Show notification
         */
        showNotification: function(message, type) {
            type = type || 'info';

            var $notification = $('<div class="tpa-notification tpa-notification-' + type + '">')
                .text(message)
                .appendTo('body');

            setTimeout(function() {
                $notification.fadeOut(function() {
                    $(this).remove();
                });
            }, 5000);
        }
    };

    /**
     * Document ready
     */
    $(document).ready(function() {
        TPA.init();
    });

    // Expose TPA to global scope
    window.TPA = TPA;

})(jQuery);
