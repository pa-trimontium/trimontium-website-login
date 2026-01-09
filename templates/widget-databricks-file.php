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

.tpa-filter-container {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 15px;
    margin: 20px 0;
}

.tpa-company-list-box,
.tpa-filters-box {
    background: #f8f9fa;
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 15px;
}

.tpa-box-title {
    font-weight: 600;
    font-size: 14px;
    color: #333;
    margin-bottom: 10px;
    padding-bottom: 8px;
    border-bottom: 2px solid #0073aa;
}

.tpa-company-count {
    font-size: 12px;
    color: #666;
    margin-left: 10px;
    font-weight: normal;
}

.tpa-company-list {
    max-height: 300px;
    overflow-y: auto;
    margin-top: 10px;
    background: white;
    border: 1px solid #ddd;
    border-radius: 4px;
    padding: 5px;
}

.tpa-company-item {
    padding: 6px 10px;
    cursor: pointer;
    border-bottom: 1px solid #f0f0f0;
    font-size: 13px;
    transition: background 0.2s;
}

.tpa-company-item:last-child {
    border-bottom: none;
}

.tpa-company-item:hover {
    background: #e8f4f8;
}

.tpa-company-item.active {
    background: #0073aa;
    color: white;
    font-weight: 600;
}

.tpa-filter-group {
    margin-bottom: 15px;
}

.tpa-filter-label {
    font-weight: 600;
    font-size: 13px;
    color: #555;
    margin-bottom: 8px;
    display: block;
}

.tpa-filter-inputs {
    display: flex;
    gap: 10px;
    align-items: center;
}

.tpa-filter-inputs input[type="number"] {
    padding: 6px 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
    width: 100px;
    font-size: 13px;
}

.tpa-filter-inputs label {
    font-size: 12px;
    color: #666;
}

.tpa-filter-actions {
    margin-top: 15px;
    display: flex;
    gap: 10px;
}

.tpa-filter-actions button {
    padding: 6px 15px;
    background: #0073aa;
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 13px;
    transition: background 0.3s;
}

.tpa-filter-actions button:hover {
    background: #005a87;
}

.tpa-filter-actions button.secondary {
    background: #666;
}

.tpa-filter-actions button.secondary:hover {
    background: #444;
}

.tpa-postcode-areas {
    display: grid;
    grid-template-columns: repeat(8, 1fr);
    gap: 5px;
    margin-top: 8px;
    max-height: 150px;
    overflow-y: auto;
    padding: 8px;
    background: white;
    border: 1px solid #ddd;
    border-radius: 4px;
}

.tpa-postcode-area-item {
    display: flex;
    align-items: center;
    font-size: 11px;
}

.tpa-postcode-area-item input[type="checkbox"] {
    margin-right: 3px;
    cursor: pointer;
}

.tpa-postcode-area-item label {
    cursor: pointer;
    margin: 0;
}

.tpa-postcode-area-item.north-west label {
    color: #cc0000;
    font-weight: 600;
}

.tpa-postcode-actions {
    margin-top: 8px;
    display: flex;
    gap: 8px;
}

.tpa-postcode-actions button {
    padding: 4px 10px;
    background: #e0e0e0;
    color: #333;
    border: none;
    border-radius: 3px;
    cursor: pointer;
    font-size: 11px;
}

.tpa-postcode-actions button:hover {
    background: #d0d0d0;
}

.tpa-filter-checkbox {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-top: 8px;
}

.tpa-filter-checkbox input[type="checkbox"] {
    cursor: pointer;
}

.tpa-filter-checkbox label {
    cursor: pointer;
    font-size: 13px;
    color: #555;
    margin: 0;
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

            <div class="tpa-filter-container">
                <div class="tpa-company-list-box">
                    <div class="tpa-box-title">
                        Companies
                        <span class="tpa-company-count">(0 of 0)</span>
                    </div>
                    <div class="tpa-company-list"></div>
                </div>

                <div class="tpa-filters-box">
                    <div class="tpa-box-title">Filters</div>

                    <div class="tpa-filter-group">
                        <label class="tpa-filter-label">Turnover</label>
                        <div class="tpa-filter-inputs">
                            <label>Min:</label>
                            <input type="number" id="turnover-min-<?php echo esc_attr($widget_id); ?>" value="0" min="0" step="1000">
                            <label>Max:</label>
                            <input type="number" id="turnover-max-<?php echo esc_attr($widget_id); ?>" value="" placeholder="No max">
                        </div>
                    </div>

                    <div class="tpa-filter-group">
                        <label class="tpa-filter-label">Employees</label>
                        <div class="tpa-filter-inputs">
                            <label>Min:</label>
                            <input type="number" id="employees-min-<?php echo esc_attr($widget_id); ?>" value="0" min="0" step="1">
                            <label>Max:</label>
                            <input type="number" id="employees-max-<?php echo esc_attr($widget_id); ?>" value="" placeholder="No max">
                        </div>
                    </div>

                    <div class="tpa-filter-group">
                        <label class="tpa-filter-label">Status</label>
                        <div class="tpa-filter-checkbox">
                            <input type="checkbox" id="active-only-<?php echo esc_attr($widget_id); ?>" checked>
                            <label for="active-only-<?php echo esc_attr($widget_id); ?>">Active companies only</label>
                        </div>
                    </div>

                    <div class="tpa-filter-group">
                        <label class="tpa-filter-label">Postcode Areas</label>
                        <div class="tpa-postcode-actions">
                            <button class="tpa-check-all-postcodes">Check All</button>
                            <button class="tpa-check-none-postcodes">Check None</button>
                            <button class="tpa-nw-only-postcodes">NW Only</button>
                        </div>
                        <div class="tpa-postcode-areas" id="postcode-areas-<?php echo esc_attr($widget_id); ?>">
                            <!-- Populated by JavaScript -->
                        </div>
                    </div>

                    <div class="tpa-filter-actions">
                        <button class="tpa-apply-filters">Apply Filters</button>
                        <button class="tpa-clear-filters secondary">Clear</button>
                    </div>
                </div>
            </div>

            <div class="tpa-lead-navigation" style="display: none;">
                <button class="tpa-prev-lead" disabled>← Previous</button>
                <span class="tpa-current-position">Lead <span class="current">1</span> of <span class="total">0</span></span>
                <button class="tpa-next-lead">Next →</button>
            </div>

            <div class="tpa-lead-tabs" style="display: none;">
                <button class="tpa-lead-tab active" data-tab="overview">Overview</button>
                <button class="tpa-lead-tab" data-tab="scripts">Scripts</button>
                <button class="tpa-lead-tab" data-tab="general">FAME</button>
                <button class="tpa-lead-tab" data-tab="companies-house">Companies House</button>
            </div>

            <div id="tab-overview" class="tpa-tab-content active">
                <div class="tpa-file-data-overview"></div>
            </div>

            <div id="tab-scripts" class="tpa-tab-content">
                <div class="tpa-file-data-scripts"></div>
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
        var filteredLeads = []; // Filtered list of leads
        var currentIndex = 0; // Index in filteredLeads, not allLeads
        var renderedTabs = {}; // Track which tabs have been rendered for the current lead

        // Store separate datasets for each tab
        var scriptsDataCache = {}; // Map of lead index to scripts data
        var fameDataCache = {}; // Map of lead index to fame data
        var chDataCache = {}; // Map of lead index to companies house data
        var loadedTabFiles = {}; // Track which tab files have been loaded

        var $widget = $('#' + widgetId);
        var $leadNumber = $('#lead-number-' + widgetId);
        var $turnoverMin = $('#turnover-min-' + widgetId);
        var $turnoverMax = $('#turnover-max-' + widgetId);
        var $employeesMin = $('#employees-min-' + widgetId);
        var $employeesMax = $('#employees-max-' + widgetId);
        var $activeOnly = $('#active-only-' + widgetId);
        var $postcodeAreas = $('#postcode-areas-' + widgetId);

        // North West England postcode areas
        var northWestAreas = ['BB', 'BL', 'CH', 'CW', 'FY', 'L', 'LA', 'M', 'OL', 'PR', 'SK', 'WA', 'WN'];

        // All UK postcode areas (North West areas first)
        var allPostcodeAreas = [
            // North West England
            'BB', 'BL', 'CH', 'CW', 'FY', 'L', 'LA', 'M', 'OL', 'PR', 'SK', 'WA', 'WN',
            // Rest of UK
            'AB', 'AL', 'B', 'BA', 'BD', 'BF', 'BH', 'BN', 'BR', 'BS', 'BT', 'BX',
            'CA', 'CB', 'CF', 'CM', 'CO', 'CR', 'CT', 'CV',
            'DA', 'DD', 'DE', 'DG', 'DH', 'DL', 'DN', 'DT', 'DY',
            'E', 'EC', 'EH', 'EN', 'EX',
            'FK',
            'G', 'GL', 'GU', 'GY',
            'HA', 'HD', 'HG', 'HP', 'HR', 'HS', 'HU', 'HX',
            'IG', 'IM', 'IP', 'IV',
            'JE',
            'KA', 'KT', 'KW', 'KY',
            'LD', 'LE', 'LL', 'LN', 'LS', 'LU',
            'ME', 'MK', 'ML',
            'N', 'NE', 'NG', 'NN', 'NP', 'NR', 'NW',
            'OX',
            'PA', 'PE', 'PH', 'PL', 'PO',
            'RG', 'RH', 'RM',
            'S', 'SA', 'SE', 'SG', 'SL', 'SM', 'SN', 'SO', 'SP', 'SR', 'SS', 'ST', 'SW', 'SY',
            'TA', 'TD', 'TF', 'TN', 'TQ', 'TR', 'TS', 'TW',
            'UB',
            'W', 'WC', 'WD', 'WF', 'WR', 'WS', 'WV',
            'YO',
            'ZE'
        ];

        // Initialize postcode area checkboxes
        function initPostcodeAreas() {
            $postcodeAreas.empty();

            allPostcodeAreas.forEach(function(area) {
                var checkboxId = 'postcode-' + area + '-' + widgetId;
                var $item = $('<div class="tpa-postcode-area-item">');
                var $checkbox = $('<input type="checkbox" checked>')
                    .attr('id', checkboxId)
                    .attr('value', area)
                    .addClass('tpa-postcode-checkbox');
                var $label = $('<label>')
                    .attr('for', checkboxId)
                    .text(area);

                // Add north-west class if this is a North West area
                if (northWestAreas.indexOf(area) !== -1) {
                    $item.addClass('north-west');
                }

                $item.append($checkbox).append($label);
                $postcodeAreas.append($item);
            });
        }

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

        // Load widget data - only loads overview data
        function loadFileData() {
            $widget.find('.tpa-widget-loading').show();
            $widget.find('.tpa-widget-content').hide();
            $widget.find('.tpa-widget-error').hide();

            // Determine the overview file path
            var basePath = filePath.replace(/leads\.json$/, '');
            var overviewPath = basePath + 'leads-overview.json';

            $.ajax({
                url: tpaAjax.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'tpa_load_databricks_file',
                    nonce: tpaAjax.nonce,
                    file_path: overviewPath
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

                        console.log('Loaded ' + allLeads.length + ' leads from overview');

                        // Initialize filtered leads to all leads
                        filteredLeads = allLeads.slice();

                        // Initialize postcode areas
                        initPostcodeAreas();

                        // Populate company list
                        populateCompanyList();

                        currentIndex = 0;
                        showLead(currentIndex);
                        updateTimestamp();
                        $widget.find('.tpa-widget-loading').hide();
                        $widget.find('.tpa-widget-content').show();
                    } else {
                        showError(response.data.message || 'Failed to load overview file');
                    }
                },
                error: function() {
                    showError('Network error occurred');
                }
            });
        }

        // Load tab data from separate JSON files
        function loadTabData(tabName, callback) {
            var basePath = filePath.replace(/leads\.json$/, '');
            var tabFilePath;

            switch(tabName) {
                case 'scripts':
                    tabFilePath = basePath + 'leads-scripts.json';
                    break;
                case 'general':
                    tabFilePath = basePath + 'leads-fame.json';
                    break;
                case 'companies-house':
                    tabFilePath = basePath + 'leads-companies-house.json';
                    break;
                default:
                    return;
            }

            // Check if we've already loaded this tab file
            if (loadedTabFiles[tabName]) {
                callback();
                return;
            }

            $.ajax({
                url: tpaAjax.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'tpa_load_databricks_file',
                    nonce: tpaAjax.nonce,
                    file_path: tabFilePath
                },
                success: function(response) {
                    if (response.success) {
                        var data = response.data;

                        // Handle different JSON structures
                        if (Array.isArray(data)) {
                            // Do nothing, we'll use data as is
                        } else if (typeof data === 'object' && data !== null) {
                            if (Array.isArray(data.leads)) {
                                data = data.leads;
                            } else if (Array.isArray(data.data)) {
                                data = data.data;
                            } else if (Array.isArray(data.results)) {
                                data = data.results;
                            } else if (Array.isArray(data.items)) {
                                data = data.items;
                            }
                        }

                        // Store data in the appropriate cache
                        if (Array.isArray(data)) {
                            data.forEach(function(item, index) {
                                switch(tabName) {
                                    case 'scripts':
                                        scriptsDataCache[index] = item;
                                        break;
                                    case 'general':
                                        fameDataCache[index] = item;
                                        break;
                                    case 'companies-house':
                                        chDataCache[index] = item;
                                        break;
                                }
                            });
                        }

                        loadedTabFiles[tabName] = true;
                        console.log('Loaded ' + tabName + ' data');
                        callback();
                    } else {
                        console.error('Failed to load ' + tabName + ' data: ' + (response.data.message || 'Unknown error'));
                        callback();
                    }
                },
                error: function() {
                    console.error('Network error loading ' + tabName + ' data');
                    callback();
                }
            });
        }

        // Populate the company list
        function populateCompanyList() {
            var $list = $widget.find('.tpa-company-list');
            var $count = $widget.find('.tpa-company-count');

            $list.empty();

            if (filteredLeads.length === 0) {
                $list.html('<div style="padding: 20px; text-align: center; color: #999;">No companies match the filters</div>');
                $count.text('(0 of ' + allLeads.length + ')');
                return;
            }

            filteredLeads.forEach(function(lead, index) {
                var companyName = getCompanyName(lead);
                var $item = $('<div class="tpa-company-item">')
                    .text(companyName)
                    .data('index', index);

                if (index === currentIndex) {
                    $item.addClass('active');
                }

                $list.append($item);
            });

            $count.text('(' + filteredLeads.length + ' of ' + allLeads.length + ')');
        }

        // Get company name from lead data
        function getCompanyName(lead) {
            // Try different possible field names
            if (lead.business_name) return lead.business_name;
            if (lead.Business_Name) return lead.Business_Name;
            if (lead.company_name) return lead.company_name;
            if (lead.Company_Name) return lead.Company_Name;
            if (lead.name) return lead.name;
            if (lead.Name) return lead.Name;

            // Fallback to registered number
            if (lead.registered_number) return 'Company ' + lead.registered_number;
            if (lead.Registered_Number) return 'Company ' + lead.Registered_Number;

            return 'Unknown Company';
        }

        // Get turnover value from lead data
        function getTurnover(lead) {
            if (lead.turnover !== undefined && lead.turnover !== null) {
                return parseFloat(lead.turnover);
            }
            if (lead.Turnover !== undefined && lead.Turnover !== null) {
                return parseFloat(lead.Turnover);
            }
            return 0;
        }

        // Get employee count from lead data
        function getEmployees(lead) {
            if (lead.employees !== undefined && lead.employees !== null) {
                return parseFloat(lead.employees);
            }
            if (lead.Employees !== undefined && lead.Employees !== null) {
                return parseFloat(lead.Employees);
            }
            if (lead.employee_count !== undefined && lead.employee_count !== null) {
                return parseFloat(lead.employee_count);
            }
            return 0;
        }

        // Get active status from lead data
        function isActive(lead) {
            var status = lead.company_status || lead.Company_Status || lead.status || lead.Status || '';
            if (typeof status === 'string') {
                status = status.toLowerCase();
                return status === 'active' || status === 'live';
            }
            if (typeof status === 'boolean') {
                return status;
            }
            // Default to true if no status field
            return true;
        }

        // Get postcode area from lead data
        function getPostcodeArea(lead) {
            var postcode = lead.postcode || lead.Postcode || lead.postal_code || lead.Postal_Code || '';
            if (!postcode) return '';

            // Extract the area (letters before the first digit)
            var match = String(postcode).trim().match(/^([A-Z]+)/i);
            return match ? match[1].toUpperCase() : '';
        }

        // Apply filters
        function applyFilters() {
            var minTurnover = parseFloat($turnoverMin.val()) || 0;
            var maxTurnover = parseFloat($turnoverMax.val()) || Infinity;
            var minEmployees = parseFloat($employeesMin.val()) || 0;
            var maxEmployees = parseFloat($employeesMax.val()) || Infinity;
            var activeOnlyChecked = $activeOnly.is(':checked');

            // Get selected postcode areas
            var selectedPostcodes = [];
            $postcodeAreas.find('.tpa-postcode-checkbox:checked').each(function() {
                selectedPostcodes.push($(this).val());
            });

            filteredLeads = allLeads.filter(function(lead) {
                // Turnover filter
                var turnover = getTurnover(lead);
                if (turnover < minTurnover || turnover > maxTurnover) {
                    return false;
                }

                // Employees filter
                var employees = getEmployees(lead);
                if (employees < minEmployees || employees > maxEmployees) {
                    return false;
                }

                // Active status filter
                if (activeOnlyChecked && !isActive(lead)) {
                    return false;
                }

                // Postcode area filter
                if (selectedPostcodes.length > 0) {
                    var postcodeArea = getPostcodeArea(lead);
                    if (!postcodeArea || selectedPostcodes.indexOf(postcodeArea) === -1) {
                        return false;
                    }
                }

                return true;
            });

            // Reset to first lead in filtered list
            currentIndex = 0;

            // Update UI
            populateCompanyList();
            if (filteredLeads.length > 0) {
                showLead(0);
            } else {
                $widget.find('.tpa-lead-navigation').hide();
                $widget.find('.tpa-lead-tabs').hide();
                $widget.find('.tpa-file-data-overview').html('<div class="tpa-lead-no-data">No leads match the current filters</div>');
            }
        }

        // Clear filters
        function clearFilters() {
            $turnoverMin.val(0);
            $turnoverMax.val('');
            $employeesMin.val(0);
            $employeesMax.val('');
            $activeOnly.prop('checked', true);

            // Check all postcode areas
            $postcodeAreas.find('.tpa-postcode-checkbox').prop('checked', true);

            filteredLeads = allLeads.slice();
            currentIndex = 0;

            populateCompanyList();
            if (filteredLeads.length > 0) {
                showLead(0);
            }
        }

        function showLead(index) {
            if (!filteredLeads || filteredLeads.length === 0) {
                $widget.find('.tpa-file-data-general').html('<div class="tpa-lead-no-data">No leads found</div>');
                $widget.find('.tpa-lead-navigation').hide();
                $widget.find('.tpa-lead-tabs').hide();
                return;
            }

            if (index < 0 || index >= filteredLeads.length) {
                return;
            }

            currentIndex = index;
            var lead = filteredLeads[index];

            // Update navigation
            $widget.find('.tpa-lead-navigation').show();
            $widget.find('.tpa-current-position .current').text(index + 1);
            $widget.find('.tpa-current-position .total').text(filteredLeads.length);
            $widget.find('.tpa-prev-lead').prop('disabled', index === 0);
            $widget.find('.tpa-next-lead').prop('disabled', index === filteredLeads.length - 1);

            // Update lead number input
            $leadNumber.val(index + 1);

            // Update company list highlighting
            $widget.find('.tpa-company-item').removeClass('active');
            $widget.find('.tpa-company-item').eq(index).addClass('active');

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

        function renderScriptsData(scriptsData) {
            if (!scriptsData || Object.keys(scriptsData).length === 0) {
                $widget.find('.tpa-file-data-scripts').html('<div class="tpa-lead-no-data">No scripts available</div>');
                return;
            }

            var html = '<div class="tpa-lead-card">';

            // Define the order and custom labels for script fields
            var scriptFields = [
                { key: 'call_script', label: 'Call Script' },
                { key: 'email_template', label: 'Email Template' }
            ];

            var foundAny = false;

            scriptFields.forEach(function(field) {
                // Try to find the field in the scripts data (case-insensitive)
                var value = null;

                for (var key in scriptsData) {
                    if (scriptsData.hasOwnProperty(key)) {
                        if (key.toLowerCase().replace(/ /g, '_') === field.key.toLowerCase()) {
                            value = scriptsData[key];
                            foundAny = true;
                            break;
                        }
                    }
                }

                if (value !== null && value !== undefined && value !== '') {
                    var formattedValue = formatValue(value);

                    html += '<div class="tpa-lead-field">';
                    html += '<div class="tpa-lead-field-label">' + escapeHtml(field.label) + ':</div>';
                    html += '<div class="tpa-lead-field-value">' + formattedValue + '</div>';
                    html += '</div>';
                }
            });

            html += '</div>';

            if (!foundAny) {
                html = '<div class="tpa-lead-no-data">No scripts available</div>';
            }

            $widget.find('.tpa-file-data-scripts').html(html);
        }

        function renderLead(lead) {
            // Show tabs
            $widget.find('.tpa-lead-tabs').show();

            // Reset rendered tabs tracking for new lead
            renderedTabs = {
                overview: true // Overview is always rendered initially
            };

            // Render overview data immediately
            renderOverviewData(lead);
        }

        function renderTabContent(tabName) {
            // If already rendered for this lead, do nothing
            if (renderedTabs[tabName]) {
                return;
            }

            // Load tab data if needed, then render
            loadTabData(tabName, function() {
                var data = null;

                // Find the original index in allLeads for the current filtered lead
                var lead = filteredLeads[currentIndex];
                var originalIndex = allLeads.indexOf(lead);

                switch(tabName) {
                    case 'scripts':
                        data = scriptsDataCache[originalIndex];
                        renderScriptsData(data);
                        break;
                    case 'general':
                        data = fameDataCache[originalIndex];
                        renderGeneralData(data);
                        break;
                    case 'companies-house':
                        data = chDataCache[originalIndex];
                        renderCompaniesHouseData(data);
                        break;
                }

                renderedTabs[tabName] = true;
            });
        }

        function renderGeneralData(data) {
            if (!data || (typeof data === 'object' && Object.keys(data).length === 0)) {
                $widget.find('.tpa-file-data-general').html('<div class="tpa-lead-no-data">No FAME data available</div>');
                return;
            }

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
            if (!filteredLeads || filteredLeads.length === 0) {
                return;
            }

            // Hide navigation and tabs when showing all leads
            $widget.find('.tpa-lead-navigation').hide();
            $widget.find('.tpa-lead-tabs').hide();
            $widget.find('.tpa-tab-content').removeClass('active');

            var html = '<div style="max-height: 500px; overflow-y: auto;">';
            html += '<table class="tpa-data-table">';

            // Header
            var keys = Object.keys(filteredLeads[0]);
            html += '<thead><tr>';
            html += '<th style="position: sticky; top: 0; background: #f7f7f7; z-index: 1;">#</th>';
            keys.forEach(function(key) {
                var label = key.replace(/_/g, ' ');
                html += '<th style="position: sticky; top: 0; background: #f7f7f7; z-index: 1;">' + escapeHtml(label) + '</th>';
            });
            html += '</tr></thead>';

            // Body
            html += '<tbody>';
            filteredLeads.forEach(function(lead, idx) {
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
            if (leadNum && leadNum > 0 && leadNum <= filteredLeads.length) {
                showLead(leadNum - 1);
            } else {
                alert('Please enter a valid lead number between 1 and ' + filteredLeads.length);
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

            // Render tab content if not already rendered (lazy loading)
            if (!renderedTabs[tabName]) {
                renderTabContent(tabName);
            }
        });

        // Filter buttons
        $widget.find('.tpa-apply-filters').on('click', function() {
            applyFilters();
        });

        $widget.find('.tpa-clear-filters').on('click', function() {
            clearFilters();
        });

        // Company list item click
        $widget.on('click', '.tpa-company-item', function() {
            var index = $(this).data('index');
            showLead(index);
        });

        // Postcode area check all/none
        $widget.find('.tpa-check-all-postcodes').on('click', function() {
            $postcodeAreas.find('.tpa-postcode-checkbox').prop('checked', true);
        });

        $widget.find('.tpa-check-none-postcodes').on('click', function() {
            $postcodeAreas.find('.tpa-postcode-checkbox').prop('checked', false);
        });

        $widget.find('.tpa-nw-only-postcodes').on('click', function() {
            $postcodeAreas.find('.tpa-postcode-checkbox').prop('checked', false);
            var northWestAreas = ['BB', 'BL', 'CH', 'CW', 'FY', 'L', 'LA', 'M', 'OL', 'PR', 'SK', 'WA', 'WN'];
            northWestAreas.forEach(function(area) {
                $postcodeAreas.find('.tpa-postcode-checkbox[value="' + area + '"]').prop('checked', true);
            });
        });

        // Initial load
        loadFileData();
    });
})(jQuery);
</script>
