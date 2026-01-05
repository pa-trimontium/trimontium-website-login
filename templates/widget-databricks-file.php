<?php
/**
 * Template for Databricks File Widget - Lead Viewer
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

$widget_id = 'databricks-file-widget-' . uniqid();
$file_path = $atts['file_path'];
$title = $atts['title'];
$height = $atts['height'];
$display = $atts['display'];
?>

<style>
.tpa-lead-viewer {
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
}

.tpa-lead-search {
    margin: 20px 0;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 8px;
    display: flex;
    gap: 10px;
    align-items: center;
}

.tpa-lead-search label {
    font-weight: 600;
    color: #333;
}

.tpa-lead-search input[type="number"] {
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
    width: 120px;
    font-size: 14px;
}

.tpa-lead-search button {
    padding: 8px 20px;
    background: #0073aa;
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 14px;
    transition: background 0.3s;
}

.tpa-lead-search button:hover {
    background: #005a87;
}

.tpa-lead-navigation {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin: 15px 0;
    padding: 10px;
    background: #f0f0f0;
    border-radius: 4px;
}

.tpa-lead-navigation button {
    padding: 6px 15px;
    background: #0073aa;
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 13px;
}

.tpa-lead-navigation button:disabled {
    background: #ccc;
    cursor: not-allowed;
}

.tpa-lead-navigation span {
    font-weight: 600;
    color: #333;
}

.tpa-lead-card {
    background: white;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    padding: 20px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.tpa-lead-field {
    display: grid;
    grid-template-columns: 200px 1fr;
    gap: 15px;
    padding: 12px 0;
    border-bottom: 1px solid #f0f0f0;
}

.tpa-lead-field:last-child {
    border-bottom: none;
}

.tpa-lead-field-label {
    font-weight: 600;
    color: #555;
    text-transform: capitalize;
}

.tpa-lead-field-value {
    color: #333;
    word-break: break-word;
}

.tpa-lead-no-data {
    text-align: center;
    padding: 40px;
    color: #999;
}

.tpa-last-updated {
    font-size: 12px;
    color: #666;
    font-weight: normal;
    margin-left: 10px;
}

.tpa-widget-footer {
    padding: 10px 20px;
    background: #f9f9f9;
    border-top: 1px solid #e0e0e0;
    font-size: 11px;
    color: #999;
    text-align: right;
}
</style>

<div class="tpa-widget tpa-databricks-file-widget tpa-lead-viewer" id="<?php echo esc_attr($widget_id); ?>">
    <div class="tpa-widget-header">
        <h3 class="tpa-widget-title">
            <?php echo esc_html($title); ?>
            <span class="tpa-last-updated" data-timestamp=""></span>
        </h3>
        <button type="button" class="tpa-widget-refresh" data-widget-id="<?php echo esc_attr($widget_id); ?>">
            <span class="dashicons dashicons-update"></span>
        </button>
    </div>

    <div class="tpa-widget-body">
        <div class="tpa-widget-loading">
            <span class="spinner is-active"></span>
            <p><?php _e('Loading leads...', 'trimontium-website-login'); ?></p>
        </div>

        <div class="tpa-widget-content" style="display: none;">
            <div class="tpa-lead-search">
                <label for="lead-number-<?php echo esc_attr($widget_id); ?>">Lead Number:</label>
                <input type="number" id="lead-number-<?php echo esc_attr($widget_id); ?>" min="1" placeholder="Enter number">
                <button class="tpa-search-lead">View Lead</button>
                <button class="tpa-show-all">Show All</button>
            </div>

            <div class="tpa-lead-navigation" style="display: none;">
                <button class="tpa-prev-lead" disabled>← Previous</button>
                <span class="tpa-current-position">Lead <span class="current">1</span> of <span class="total">0</span></span>
                <button class="tpa-next-lead">Next →</button>
            </div>

            <div class="tpa-file-data"></div>
        </div>

        <div class="tpa-widget-error" style="display: none;">
            <p class="error-message"></p>
        </div>
    </div>

    <div class="tpa-widget-footer">
        <?php
        // Check for .synced file first (created during sync), otherwise use plugin file
        $sync_file = TPA_PLUGIN_DIR . '.synced';
        $plugin_file = TPA_PLUGIN_DIR . 'trimontium-website-login.php';

        if (file_exists($sync_file)) {
            $mod_time = filemtime($sync_file);
            $formatted_time = date('d M Y H:i:s', $mod_time);
            echo 'Plugin synced: ' . esc_html($formatted_time);
        } elseif (file_exists($plugin_file)) {
            $mod_time = filemtime($plugin_file);
            $formatted_time = date('d M Y H:i:s', $mod_time);
            echo 'Plugin code: ' . esc_html($formatted_time);
        }
        ?>
    </div>
</div>

<script>
(function($) {
    $(document).ready(function() {
        var widgetId = '<?php echo esc_js($widget_id); ?>';
        var filePath = '<?php echo esc_js($file_path); ?>';
        var allLeads = [];
        var currentIndex = 0;

        var $widget = $('#' + widgetId);
        var $leadNumber = $('#lead-number-' + widgetId);

        // Update timestamp
        function updateTimestamp() {
            var now = new Date();
            var timeString = now.toLocaleTimeString('en-GB', {
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            });
            var dateString = now.toLocaleDateString('en-GB', {
                day: '2-digit',
                month: 'short',
                year: 'numeric'
            });
            $widget.find('.tpa-last-updated').text('(Last updated: ' + dateString + ' ' + timeString + ')');
        }

        // Load widget data
        function loadFileData() {
            $widget.find('.tpa-widget-loading').show();
            $widget.find('.tpa-widget-content').hide();
            $widget.find('.tpa-widget-error').hide();

            $.ajax({
                url: tpaAjax.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'tpa_load_databricks_file',
                    nonce: tpaAjax.nonce,
                    file_path: filePath
                },
                success: function(response) {
                    if (response.success) {
                        // Handle different JSON structures
                        var data = response.data;

                        // If it's already an array, use it
                        if (Array.isArray(data)) {
                            allLeads = data;
                        }
                        // If it's an object, look for common array properties
                        else if (typeof data === 'object' && data !== null) {
                            if (Array.isArray(data.leads)) {
                                allLeads = data.leads;
                            } else if (Array.isArray(data.data)) {
                                allLeads = data.data;
                            } else if (Array.isArray(data.results)) {
                                allLeads = data.results;
                            } else if (Array.isArray(data.items)) {
                                allLeads = data.items;
                            } else {
                                // If we can't find an array, treat the object as a single lead
                                allLeads = [data];
                            }
                        }
                        // Fallback: wrap in array
                        else {
                            allLeads = [data];
                        }

                        console.log('Loaded ' + allLeads.length + ' leads');
                        currentIndex = 0;
                        showLead(currentIndex);
                        updateTimestamp();
                        $widget.find('.tpa-widget-loading').hide();
                        $widget.find('.tpa-widget-content').show();
                    } else {
                        showError(response.data.message || 'Failed to load file');
                    }
                },
                error: function() {
                    showError('Network error occurred');
                }
            });
        }

        function showLead(index) {
            if (!allLeads || allLeads.length === 0) {
                $widget.find('.tpa-file-data').html('<div class="tpa-lead-no-data">No leads found</div>');
                $widget.find('.tpa-lead-navigation').hide();
                return;
            }

            if (index < 0 || index >= allLeads.length) {
                return;
            }

            currentIndex = index;
            var lead = allLeads[index];

            // Update navigation
            $widget.find('.tpa-lead-navigation').show();
            $widget.find('.tpa-current-position .current').text(index + 1);
            $widget.find('.tpa-current-position .total').text(allLeads.length);
            $widget.find('.tpa-prev-lead').prop('disabled', index === 0);
            $widget.find('.tpa-next-lead').prop('disabled', index === allLeads.length - 1);

            // Update lead number input
            $leadNumber.val(index + 1);

            // Render lead
            renderLead(lead);
        }

        function formatValue(value) {
            if (value === null || value === undefined) {
                return '-';
            }

            if (typeof value === 'boolean') {
                return value ? 'Yes' : 'No';
            }

            if (Array.isArray(value)) {
                if (value.length === 0) {
                    return '-';
                }
                // Format array as bulleted list
                var items = value.map(function(item) {
                    if (typeof item === 'object' && item !== null) {
                        return formatObjectValue(item);
                    }
                    return formatStringWithNewlines(String(item));
                });
                return '<ul style="margin: 0; padding-left: 20px;">' +
                       items.map(function(item) { return '<li>' + item + '</li>'; }).join('') +
                       '</ul>';
            }

            if (typeof value === 'object') {
                return formatObjectValue(value);
            }

            return formatStringWithNewlines(String(value));
        }

        function formatObjectValue(obj) {
            if (!obj || Object.keys(obj).length === 0) {
                return '-';
            }

            var html = '<div style="padding-left: 10px;">';
            for (var k in obj) {
                if (obj.hasOwnProperty(k)) {
                    var v = obj[k];
                    var formattedKey = k.replace(/_/g, ' ');

                    if (v === null || v === undefined) {
                        v = '-';
                    } else if (typeof v === 'object') {
                        if (Array.isArray(v)) {
                            v = v.join(', ');
                        } else {
                            v = JSON.stringify(v);
                        }
                    } else if (typeof v === 'boolean') {
                        v = v ? 'Yes' : 'No';
                    }

                    html += '<div style="margin: 3px 0;"><strong>' + escapeHtml(formattedKey) + ':</strong> ' + formatStringWithNewlines(String(v)) + '</div>';
                }
            }
            html += '</div>';
            return html;
        }

        function formatCurrency(value) {
            var num = parseFloat(value);
            if (isNaN(num)) {
                return escapeHtml(String(value));
            }
            // Round to nearest pound
            num = Math.round(num);
            // Add thousand separators
            var formatted = num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
            return '£' + formatted;
        }

        function renderLead(lead) {
            var html = '<div class="tpa-lead-card">';

            for (var key in lead) {
                if (lead.hasOwnProperty(key)) {
                    var value = lead[key];
                    var formattedValue;

                    // Special formatting for specific fields
                    if (key.toLowerCase() === 'turnover') {
                        formattedValue = formatCurrency(value);
                    } else if (key.toLowerCase() === 'contacts') {
                        // Format contacts without bullet points
                        if (Array.isArray(value)) {
                            if (value.length === 0) {
                                formattedValue = '-';
                            } else {
                                formattedValue = '<div style="padding-left: 10px;">';
                                value.forEach(function(contact) {
                                    if (typeof contact === 'object' && contact !== null) {
                                        for (var k in contact) {
                                            if (contact.hasOwnProperty(k)) {
                                                var v = contact[k];
                                                var formattedKey = k.replace(/_/g, ' ');

                                                if (v === null || v === undefined) {
                                                    v = '-';
                                                } else if (typeof v === 'object') {
                                                    if (Array.isArray(v)) {
                                                        v = v.join(', ');
                                                    } else {
                                                        v = JSON.stringify(v);
                                                    }
                                                } else if (typeof v === 'boolean') {
                                                    v = v ? 'Yes' : 'No';
                                                }

                                                formattedValue += '<div style="margin: 3px 0;"><strong>' + escapeHtml(formattedKey) + ':</strong> ' + formatStringWithNewlines(String(v)) + '</div>';
                                            }
                                        }
                                        // Add separator between contacts if there are multiple
                                        if (value.length > 1) {
                                            formattedValue += '<hr style="margin: 8px 0; border: none; border-top: 1px solid #e0e0e0;">';
                                        }
                                    } else {
                                        formattedValue += '<div>' + formatStringWithNewlines(String(contact)) + '</div>';
                                    }
                                });
                                formattedValue += '</div>';
                            }
                        } else if (typeof value === 'object') {
                            formattedValue = formatObjectValue(value);
                        } else {
                            formattedValue = formatStringWithNewlines(String(value));
                        }
                    } else {
                        formattedValue = formatValue(value);
                    }

                    // Format the key (remove underscores, capitalize)
                    var label = key.replace(/_/g, ' ');

                    html += '<div class="tpa-lead-field">';
                    html += '<div class="tpa-lead-field-label">' + escapeHtml(label) + ':</div>';
                    html += '<div class="tpa-lead-field-value">' + formattedValue + '</div>';
                    html += '</div>';
                }
            }

            html += '</div>';
            $widget.find('.tpa-file-data').html(html);
        }

        function showAllLeads() {
            if (!allLeads || allLeads.length === 0) {
                return;
            }

            $widget.find('.tpa-lead-navigation').hide();

            var html = '<div style="max-height: 500px; overflow-y: auto;">';
            html += '<table class="tpa-data-table">';

            // Header
            var keys = Object.keys(allLeads[0]);
            html += '<thead><tr>';
            html += '<th style="position: sticky; top: 0; background: #f7f7f7; z-index: 1;">#</th>';
            keys.forEach(function(key) {
                var label = key.replace(/_/g, ' ');
                html += '<th style="position: sticky; top: 0; background: #f7f7f7; z-index: 1;">' + escapeHtml(label) + '</th>';
            });
            html += '</tr></thead>';

            // Body
            html += '<tbody>';
            allLeads.forEach(function(lead, idx) {
                html += '<tr>';
                html += '<td>' + (idx + 1) + '</td>';
                keys.forEach(function(key) {
                    var value = lead[key];
                    if (value === null || value === undefined) {
                        value = '-';
                    } else if (typeof value === 'object') {
                        value = JSON.stringify(value);
                    }
                    html += '<td>' + escapeHtml(String(value)) + '</td>';
                });
                html += '</tr>';
            });
            html += '</tbody></table></div>';

            $widget.find('.tpa-file-data').html(html);
        }

        function escapeHtml(text) {
            var map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return text.replace(/[&<>"']/g, function(m) { return map[m]; });
        }

        function formatStringWithNewlines(text) {
            // First escape HTML to prevent XSS
            var escaped = escapeHtml(text);
            // Then convert actual newline characters to <br> tags
            return escaped.replace(/\n/g, '<br>');
        }

        function showError(message) {
            $widget.find('.tpa-widget-loading').hide();
            $widget.find('.tpa-widget-error').show();
            $widget.find('.error-message').text(message);
        }

        // Event handlers
        $widget.find('.tpa-prev-lead').on('click', function() {
            if (currentIndex > 0) {
                showLead(currentIndex - 1);
            }
        });

        $widget.find('.tpa-next-lead').on('click', function() {
            if (currentIndex < allLeads.length - 1) {
                showLead(currentIndex + 1);
            }
        });

        $widget.find('.tpa-search-lead').on('click', function() {
            var leadNum = parseInt($leadNumber.val());
            if (leadNum && leadNum > 0 && leadNum <= allLeads.length) {
                showLead(leadNum - 1);
            } else {
                alert('Please enter a valid lead number between 1 and ' + allLeads.length);
            }
        });

        $widget.find('.tpa-show-all').on('click', function() {
            showAllLeads();
        });

        $leadNumber.on('keypress', function(e) {
            if (e.which === 13) { // Enter key
                $widget.find('.tpa-search-lead').click();
            }
        });

        $widget.find('.tpa-widget-refresh').on('click', function() {
            loadFileData();
        });

        // Initial load
        loadFileData();
    });
})(jQuery);
</script>
