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

.tpa-lead-tabs {
    display: flex;
    gap: 5px;
    border-bottom: 2px solid #e0e0e0;
    margin-bottom: 20px;
}

.tpa-lead-tab {
    padding: 10px 20px;
    background: #f5f5f5;
    border: none;
    border-top-left-radius: 4px;
    border-top-right-radius: 4px;
    cursor: pointer;
    font-size: 14px;
    font-weight: 500;
    color: #666;
    transition: all 0.3s;
}

.tpa-lead-tab:hover {
    background: #e8e8e8;
    color: #333;
}

.tpa-lead-tab.active {
    background: white;
    color: #0073aa;
    border-bottom: 2px solid #0073aa;
    margin-bottom: -2px;
}

.tpa-tab-content {
    display: none;
}

.tpa-tab-content.active {
    display: block;
}

.tpa-ch-section {
    margin-bottom: 25px;
    padding: 15px;
    background: #fafafa;
    border-radius: 6px;
    border-left: 3px solid #0073aa;
}

.tpa-ch-section-title {
    font-size: 16px;
    font-weight: 600;
    color: #333;
    margin-bottom: 12px;
    padding-bottom: 8px;
    border-bottom: 1px solid #e0e0e0;
}

.tpa-ch-field {
    display: grid;
    grid-template-columns: 180px 1fr;
    gap: 12px;
    padding: 8px 0;
    border-bottom: 1px solid #f0f0f0;
}

.tpa-ch-field:last-child {
    border-bottom: none;
}

.tpa-ch-field-label {
    font-weight: 600;
    color: #555;
}

.tpa-ch-field-value {
    color: #333;
    word-break: break-word;
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
            <p><?php _e('Loading leads...', 'trimontium-wp-private-dashboards'); ?></p>
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

            <div class="tpa-lead-tabs" style="display: none;">
                <button class="tpa-lead-tab active" data-tab="overview">Overview</button>
                <button class="tpa-lead-tab" data-tab="general">FAME</button>
                <button class="tpa-lead-tab" data-tab="companies-house">Companies House</button>
            </div>

            <div id="tab-overview" class="tpa-tab-content active">
                <div class="tpa-file-data-overview"></div>
            </div>

            <div id="tab-general" class="tpa-tab-content">
                <div class="tpa-file-data-general"></div>
            </div>

            <div id="tab-companies-house" class="tpa-tab-content">
                <div class="tpa-file-data-ch"></div>
            </div>
        </div>

        <div class="tpa-widget-error" style="display: none;">
            <p class="error-message"></p>
        </div>
    </div>

    <div class="tpa-widget-footer">
        <?php
        // Check for .synced file first (created during sync), otherwise use plugin file
        $sync_file = TPA_PLUGIN_DIR . '.synced';
        $plugin_file = TPA_PLUGIN_DIR . 'trimontium-wp-private-dashboards.php';

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
                $widget.find('.tpa-file-data-general').html('<div class="tpa-lead-no-data">No leads found</div>');
                $widget.find('.tpa-lead-navigation').hide();
                $widget.find('.tpa-lead-tabs').hide();
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

            // Reset to first tab (Overview)
            $widget.find('.tpa-lead-tab').removeClass('active');
            $widget.find('.tpa-lead-tab[data-tab="overview"]').addClass('active');
            $widget.find('.tpa-tab-content').removeClass('active');
            $widget.find('#tab-overview').addClass('active');

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

        function renderOverviewData(lead) {
            // Define the scoring fields (top section with box)
            var scoringFields = [
                'rank',
                'score',
                'explanation'
            ];

            // Define the company info fields (bottom section)
            var companyFields = [
                'business_name',
                'registered_number',
                'postcode',
                'company_status',
                'legal_form',
                'trade_description',
                'sic_description',
                'email',
                'website',
                'turnover',
                'employees'
            ];

            var html = '';
            var foundAny = false;

            // Render scoring section with box
            var scoringHtml = '<div class="tpa-lead-card" style="background: #f0f7ff; border: 2px solid #0073aa; margin-bottom: 20px;">';
            var foundScoring = false;

            scoringFields.forEach(function(fieldKey) {
                // Try to find the field in the lead data (case-insensitive)
                var value = null;

                for (var key in lead) {
                    if (lead.hasOwnProperty(key)) {
                        if (key.toLowerCase().replace(/ /g, '_') === fieldKey.toLowerCase()) {
                            value = lead[key];
                            foundScoring = true;
                            foundAny = true;
                            break;
                        }
                    }
                }

                // Format the label - capitalize first letter
                var label = fieldKey.replace(/_/g, ' ').replace(/\b\w/g, function(l) {
                    return l.toUpperCase();
                });

                // Format the value
                var formattedValue;
                if (value === null || value === undefined) {
                    formattedValue = '-';
                } else {
                    formattedValue = formatValue(value);
                }

                scoringHtml += '<div class="tpa-lead-field">';
                scoringHtml += '<div class="tpa-lead-field-label">' + escapeHtml(label) + ':</div>';
                scoringHtml += '<div class="tpa-lead-field-value">' + formattedValue + '</div>';
                scoringHtml += '</div>';
            });

            scoringHtml += '</div>';

            if (foundScoring) {
                html += scoringHtml;
            }

            // Render company info section
            var companyHtml = '<div class="tpa-lead-card">';
            var foundCompany = false;

            companyFields.forEach(function(fieldKey) {
                // Try to find the field in the lead data (case-insensitive)
                var value = null;

                for (var key in lead) {
                    if (lead.hasOwnProperty(key)) {
                        if (key.toLowerCase().replace(/ /g, '_') === fieldKey.toLowerCase()) {
                            value = lead[key];
                            foundCompany = true;
                            foundAny = true;
                            break;
                        }
                    }
                }

                // Format the label
                var label = fieldKey.replace(/_/g, ' ').replace(/\b\w/g, function(l) {
                    return l.toUpperCase();
                });

                // Format the value
                var formattedValue;
                if (value === null || value === undefined) {
                    formattedValue = '-';
                } else if (fieldKey === 'turnover') {
                    formattedValue = formatCurrency(value);
                } else if (fieldKey === 'website' && value && value !== '-') {
                    // Make website clickable
                    var url = String(value);
                    if (!url.match(/^https?:\/\//)) {
                        url = 'http://' + url;
                    }
                    formattedValue = '<a href="' + escapeHtml(url) + '" target="_blank" rel="noopener noreferrer">' + escapeHtml(String(value)) + '</a>';
                } else if (fieldKey === 'email' && value && value !== '-') {
                    // Make email clickable
                    formattedValue = '<a href="mailto:' + escapeHtml(String(value)) + '">' + escapeHtml(String(value)) + '</a>';
                } else {
                    formattedValue = formatValue(value);
                }

                companyHtml += '<div class="tpa-lead-field">';
                companyHtml += '<div class="tpa-lead-field-label">' + escapeHtml(label) + ':</div>';
                companyHtml += '<div class="tpa-lead-field-value">' + formattedValue + '</div>';
                companyHtml += '</div>';
            });

            companyHtml += '</div>';

            if (foundCompany) {
                html += companyHtml;
            }

            if (!foundAny) {
                html = '<div class="tpa-lead-no-data">No overview data available</div>';
            }

            $widget.find('.tpa-file-data-overview').html(html);
        }

        function renderLead(lead) {
            // Show tabs
            $widget.find('.tpa-lead-tabs').show();

            // Separate companies house data from general data
            var companiesHouse = null;
            var generalData = {};

            for (var key in lead) {
                if (lead.hasOwnProperty(key)) {
                    // Check for companies house field (various spellings)
                    if (key.toLowerCase() === 'companies_house' ||
                        key.toLowerCase() === 'companie_house' ||
                        key.toLowerCase() === 'companieshouse' ||
                        key.toLowerCase() === 'companies house') {
                        companiesHouse = lead[key];
                    } else {
                        generalData[key] = lead[key];
                    }
                }
            }

            // Render overview data
            renderOverviewData(lead);

            // Render general data (FAME)
            renderGeneralData(generalData);

            // Render companies house data
            renderCompaniesHouseData(companiesHouse);
        }

        function renderGeneralData(data) {
            var html = '<div class="tpa-lead-card">';

            for (var key in data) {
                if (data.hasOwnProperty(key)) {
                    var value = data[key];
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
            $widget.find('.tpa-file-data-general').html(html);
        }

        function renderCompaniesHouseData(data) {
            if (!data || (typeof data === 'object' && Object.keys(data).length === 0)) {
                $widget.find('.tpa-file-data-ch').html('<div class="tpa-lead-no-data">No Companies House data available</div>');
                return;
            }

            var html = '<div class="tpa-lead-card">';
            html += renderCompaniesHouseSection(data);
            html += '</div>';

            $widget.find('.tpa-file-data-ch').html(html);
        }

        function renderCompaniesHouseSection(data, depth) {
            depth = depth || 0;
            var html = '';

            if (typeof data !== 'object' || data === null) {
                return escapeHtml(String(data));
            }

            // Group related fields into sections
            var sections = categorizeCompaniesHouseFields(data);

            for (var sectionName in sections) {
                if (sections.hasOwnProperty(sectionName)) {
                    var sectionData = sections[sectionName];

                    if (depth === 0) {
                        html += '<div class="tpa-ch-section">';
                        html += '<div class="tpa-ch-section-title">' + escapeHtml(sectionName) + '</div>';
                    }

                    for (var key in sectionData) {
                        if (sectionData.hasOwnProperty(key)) {
                            var value = sectionData[key];
                            var label = formatFieldLabel(key);

                            html += '<div class="tpa-ch-field">';
                            html += '<div class="tpa-ch-field-label">' + escapeHtml(label) + ':</div>';
                            html += '<div class="tpa-ch-field-value">' + formatCompaniesHouseValue(value) + '</div>';
                            html += '</div>';
                        }
                    }

                    if (depth === 0) {
                        html += '</div>';
                    }
                }
            }

            return html;
        }

        function categorizeCompaniesHouseFields(data) {
            var sections = {
                'Company Information': {},
                'Registered Office': {},
                'Company Status': {},
                'Accounts': {},
                'Officers': {},
                'Filing History': {},
                'SIC Codes': {},
                'Other Information': {}
            };

            for (var key in data) {
                if (!data.hasOwnProperty(key)) continue;

                var lowerKey = key.toLowerCase();

                // Categorize fields
                if (lowerKey.includes('name') || lowerKey.includes('number') || lowerKey.includes('type') ||
                    lowerKey.includes('jurisdiction') || lowerKey.includes('country') && !lowerKey.includes('registered')) {
                    sections['Company Information'][key] = data[key];
                } else if (lowerKey.includes('address') || lowerKey.includes('registered') ||
                           lowerKey.includes('locality') || lowerKey.includes('postal') || lowerKey.includes('region')) {
                    sections['Registered Office'][key] = data[key];
                } else if (lowerKey.includes('status') || lowerKey.includes('date') && !lowerKey.includes('account')) {
                    sections['Company Status'][key] = data[key];
                } else if (lowerKey.includes('account') || lowerKey.includes('annual_return') ||
                           lowerKey.includes('confirmation_statement')) {
                    sections['Accounts'][key] = data[key];
                } else if (lowerKey.includes('officer') || lowerKey.includes('director') || lowerKey.includes('secretary')) {
                    sections['Officers'][key] = data[key];
                } else if (lowerKey.includes('filing') || lowerKey.includes('document')) {
                    sections['Filing History'][key] = data[key];
                } else if (lowerKey.includes('sic')) {
                    sections['SIC Codes'][key] = data[key];
                } else {
                    sections['Other Information'][key] = data[key];
                }
            }

            // Remove empty sections
            for (var section in sections) {
                if (Object.keys(sections[section]).length === 0) {
                    delete sections[section];
                }
            }

            return sections;
        }

        function formatFieldLabel(key) {
            return key
                .replace(/_/g, ' ')
                .replace(/\b\w/g, function(l) { return l.toUpperCase(); });
        }

        function formatCompaniesHouseValue(value) {
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

                var html = '<div style="margin-top: 5px;">';
                value.forEach(function(item, idx) {
                    if (typeof item === 'object' && item !== null) {
                        html += '<div style="background: white; padding: 10px; margin: 5px 0; border-radius: 4px; border: 1px solid #e0e0e0;">';
                        for (var k in item) {
                            if (item.hasOwnProperty(k)) {
                                var label = formatFieldLabel(k);
                                var val = formatCompaniesHouseValue(item[k]);
                                html += '<div style="margin: 3px 0;"><strong>' + escapeHtml(label) + ':</strong> ' + val + '</div>';
                            }
                        }
                        html += '</div>';
                    } else {
                        html += '<div style="margin: 3px 0;">• ' + escapeHtml(String(item)) + '</div>';
                    }
                });
                html += '</div>';
                return html;
            }

            if (typeof value === 'object') {
                var html = '<div style="padding-left: 15px; margin-top: 5px;">';
                for (var k in value) {
                    if (value.hasOwnProperty(k)) {
                        var label = formatFieldLabel(k);
                        var val = formatCompaniesHouseValue(value[k]);
                        html += '<div style="margin: 5px 0;"><strong>' + escapeHtml(label) + ':</strong> ' + val + '</div>';
                    }
                }
                html += '</div>';
                return html;
            }

            // Format dates nicely if they look like ISO dates
            var dateMatch = String(value).match(/^(\d{4})-(\d{2})-(\d{2})/);
            if (dateMatch) {
                var date = new Date(value);
                if (!isNaN(date.getTime())) {
                    return escapeHtml(date.toLocaleDateString('en-GB', {
                        day: '2-digit',
                        month: 'short',
                        year: 'numeric'
                    }));
                }
            }

            return formatStringWithNewlines(String(value));
        }

        function showAllLeads() {
            if (!allLeads || allLeads.length === 0) {
                return;
            }

            // Hide navigation and tabs when showing all leads
            $widget.find('.tpa-lead-navigation').hide();
            $widget.find('.tpa-lead-tabs').hide();
            $widget.find('.tpa-tab-content').removeClass('active');

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

            // Show in the general tab container
            $widget.find('.tpa-file-data-general').html(html);
            $widget.find('#tab-general').addClass('active');
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

        // Tab switching
        $widget.find('.tpa-lead-tab').on('click', function() {
            var tabName = $(this).data('tab');

            // Update tab buttons
            $widget.find('.tpa-lead-tab').removeClass('active');
            $(this).addClass('active');

            // Update tab content
            $widget.find('.tpa-tab-content').removeClass('active');
            $widget.find('#tab-' + tabName).addClass('active');
        });

        // Initial load
        loadFileData();
    });
})(jQuery);
</script>
