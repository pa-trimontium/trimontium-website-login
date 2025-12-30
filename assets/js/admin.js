/**
 * Admin JavaScript for Trimontium Private Area
 */

(function($) {
    'use strict';

    var TPAAdmin = {
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
            // Test Azure connection
            $('#test-azure-connection').on('click', this.testAzureConnection);

            // Test Databricks connection
            $('#test-databricks-connection').on('click', this.testDatabricksConnection);

            // Clear cache buttons
            $('#clear-all-cache').on('click', function() {
                TPAAdmin.clearCache('all');
            });

            $('#clear-azure-cache').on('click', function() {
                TPAAdmin.clearCache('azure');
            });

            $('#clear-databricks-cache').on('click', function() {
                TPAAdmin.clearCache('databricks');
            });
        },

        /**
         * Test Azure connection
         */
        testAzureConnection: function(e) {
            e.preventDefault();

            var $button = $(this);
            var $results = $('#connection-results');

            $button.addClass('loading').prop('disabled', true);
            $results.removeClass('success error').html('Testing connection...');

            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'tpa_test_azure_connection',
                    nonce: $('#tpa_ajax_nonce').val() || ''
                },
                success: function(response) {
                    if (response.success) {
                        $results.addClass('success').html(
                            '<strong>Success!</strong> ' + response.message +
                            (response.subscription ? '<br>Subscription: ' + response.subscription : '')
                        );
                    } else {
                        $results.addClass('error').html(
                            '<strong>Error:</strong> ' + response.message
                        );
                    }
                },
                error: function() {
                    $results.addClass('error').html(
                        '<strong>Error:</strong> Network error occurred'
                    );
                },
                complete: function() {
                    $button.removeClass('loading').prop('disabled', false);
                }
            });
        },

        /**
         * Test Databricks connection
         */
        testDatabricksConnection: function(e) {
            e.preventDefault();

            var $button = $(this);
            var $results = $('#connection-results');

            $button.addClass('loading').prop('disabled', true);
            $results.removeClass('success error').html('Testing connection...');

            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'tpa_test_databricks_connection',
                    nonce: $('#tpa_ajax_nonce').val() || ''
                },
                success: function(response) {
                    if (response.success) {
                        $results.addClass('success').html(
                            '<strong>Success!</strong> ' + response.message +
                            (response.clusters !== undefined ? '<br>Clusters found: ' + response.clusters : '')
                        );
                    } else {
                        $results.addClass('error').html(
                            '<strong>Error:</strong> ' + response.message
                        );
                    }
                },
                error: function() {
                    $results.addClass('error').html(
                        '<strong>Error:</strong> Network error occurred'
                    );
                },
                complete: function() {
                    $button.removeClass('loading').prop('disabled', false);
                }
            });
        },

        /**
         * Clear cache
         */
        clearCache: function(type) {
            var $results = $('#cache-clear-results');

            $results.removeClass('success').html('Clearing cache...');

            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'tpa_clear_cache',
                    nonce: $('#tpa_ajax_nonce').val() || '',
                    type: type
                },
                success: function(response) {
                    if (response.success) {
                        $results.addClass('success').html(
                            '<strong>Success!</strong> ' + response.data.message
                        );
                    } else {
                        $results.html(
                            '<strong>Error:</strong> ' + response.data.message
                        );
                    }

                    setTimeout(function() {
                        $results.fadeOut(function() {
                            $(this).html('').show();
                        });
                    }, 3000);
                },
                error: function() {
                    $results.html(
                        '<strong>Error:</strong> Network error occurred'
                    );
                }
            });
        }
    };

    /**
     * Document ready
     */
    $(document).ready(function() {
        TPAAdmin.init();

        // Add nonce to page if not already present
        if (!$('#tpa_ajax_nonce').length && typeof wp !== 'undefined' && wp.ajax) {
            $('<input>').attr({
                type: 'hidden',
                id: 'tpa_ajax_nonce',
                value: wp.ajax.settings.nonce || ''
            }).appendTo('body');
        }
    });

})(jQuery);
