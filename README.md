# Trimontium Private Area WordPress Plugin

A secure WordPress plugin for creating a private area with role-based access control for Azure and Databricks dashboards.

## Features

- **Secure Authentication**: Leverages WordPress's built-in authentication system
- **Role-Based Access Control**: Custom user role with granular capabilities
- **Azure Integration**: Connect to Azure resources and display metrics
- **Databricks Integration**: Display Databricks jobs, clusters, and runs
- **Dashboard System**: Create custom dashboards with widgets
- **API Caching**: Configurable caching to reduce API calls
- **Access Logging**: Track API usage and access attempts
- **Shortcode Support**: Easy integration into pages with shortcodes

## Installation

1. **Upload the plugin files** to `/wp-content/plugins/trimontium-website-login/` directory
2. **Activate the plugin** through the 'Plugins' menu in WordPress
3. **Configure API credentials** in Private Area → API Credentials
4. **Create dashboard pages** using the Private Dashboards post type
5. **Assign users** to the Private Area User role

## Configuration

### Azure Setup

1. Navigate to **Private Area → API Credentials → Azure**
2. Enter your Azure credentials:
   - Tenant ID
   - Client ID
   - Client Secret
   - Subscription ID
3. Click **Test Azure Connection** to verify

### Databricks Setup

1. Navigate to **Private Area → API Credentials → Databricks**
2. Enter your Databricks credentials:
   - Workspace URL (e.g., `https://adb-xxxxx.azuredatabricks.net`)
   - Access Token
3. Click **Test Databricks Connection** to verify

## Creating Dashboards

### Method 1: Using Dashboard Post Type

1. Go to **Private Area → Add New**
2. Set the title and add content
3. In the **Dashboard Settings** meta box:
   - Check "Require authentication to view"
   - Select Dashboard Type (Azure, Databricks, or General)
   - Set Auto-refresh interval if desired
4. Publish the dashboard

### Method 2: Using Shortcodes in Regular Pages

Add shortcodes to any WordPress page:

```
[tpa_azure_widget type="metrics" resource_id="/subscriptions/xxx/resourceGroups/xxx" metric="Percentage CPU" title="CPU Usage"]

[tpa_databricks_widget type="jobs" workspace="https://adb-xxxxx.azuredatabricks.net" limit="10" title="Recent Jobs"]
```

## Shortcodes

### Azure Widget

```
[tpa_azure_widget
    type="metrics"
    resource_id="/subscriptions/xxx/resourceGroups/xxx/providers/xxx"
    metric="Percentage CPU"
    title="Azure Metrics"
    height="300px"]
```

**Parameters:**
- `type`: Widget type (currently "metrics")
- `resource_id`: Full Azure resource ID
- `metric`: Metric name to display
- `title`: Widget title (optional)
- `height`: Widget height (optional, default: 300px)

### Databricks Widget

```
[tpa_databricks_widget
    type="jobs"
    workspace="https://adb-xxxxx.azuredatabricks.net"
    limit="10"
    title="Databricks Jobs"
    height="400px"]
```

**Parameters:**
- `type`: Widget type ("jobs", "clusters", or "runs")
- `workspace`: Databricks workspace URL
- `limit`: Number of items to display (optional)
- `title`: Widget title (optional)
- `height`: Widget height (optional, default: 400px)

### Dashboard Shortcode

```
[tpa_dashboard id="custom" layout="grid" columns="2"]
```

## User Management

### Assigning Private Area Access

**Method 1: Individual Users**
1. Go to **Users → All Users**
2. Click on a user
3. In the roles section, add the "Private Area User" role

**Method 2: Bulk Actions**
1. Go to **Users → All Users**
2. Select multiple users
3. From "Bulk Actions" dropdown, select "Grant Private Area Access"
4. Click Apply

### User Capabilities

The "Private Area User" role includes:
- `access_private_area`: View private area pages
- `view_azure_dashboards`: Access Azure dashboards
- `view_databricks_dashboards`: Access Databricks dashboards

Administrators automatically have all capabilities plus:
- `manage_private_area_settings`: Configure plugin settings

## API Integration

### Azure API

The plugin uses Azure Management API with OAuth2 authentication:
- Automatically obtains and caches access tokens
- Supports metrics API for resource monitoring
- Token caching reduces authentication overhead

### Databricks API

The plugin uses Databricks REST API 2.0:
- Personal access token authentication
- Supports jobs, clusters, and runs endpoints
- Response caching for performance

## Security Features

1. **Authentication Required**: All private pages require login
2. **Capability Checks**: Role-based access at page and widget level
3. **Nonce Verification**: All AJAX requests verified
4. **Secure Storage**: API credentials stored in WordPress options (consider using environment variables for production)
5. **Access Logging**: Track who accesses what and when
6. **API Rate Limiting**: Built-in caching reduces API calls

## Customization

### Styling

Override the default styles by adding custom CSS:

```css
.tpa-dashboard-wrapper {
    /* Your custom styles */
}
```

### Templates

Copy template files from `/templates/` to your theme folder at `/your-theme/trimontium-website-login/` to customize:
- `dashboard-template.php`
- `widget-azure.php`
- `widget-databricks.php`
- `shortcode-dashboard.php`

### Hooks and Filters

The plugin provides various hooks for extending functionality (see code comments for available hooks).

## Troubleshooting

### Connection Issues

1. **Test connections** using the test buttons in API Credentials page
2. **Check credentials** are correct and not expired
3. **Verify network** allows outbound HTTPS connections
4. **Review logs** in Private Area → API Logs

### Permission Issues

1. **Check user role** includes required capabilities
2. **Verify page** is marked as private in Dashboard Settings
3. **Clear cache** if settings were recently changed

### Cache Issues

1. Navigate to **Private Area → Settings**
2. Use cache clear buttons to reset cached data
3. Adjust cache duration if needed

## Development

### File Structure

```
trimontium-website-login/
├── admin/
│   └── class-tpa-admin.php
├── assets/
│   ├── css/
│   │   ├── admin.css
│   │   └── frontend.css
│   └── js/
│       ├── admin.js
│       └── frontend.js
├── includes/
│   ├── class-tpa-api.php
│   ├── class-tpa-auth.php
│   ├── class-tpa-dashboard.php
│   └── class-tpa-roles.php
├── templates/
│   ├── dashboard-template.php
│   ├── shortcode-dashboard.php
│   ├── widget-azure.php
│   └── widget-databricks.php
├── trimontium-website-login.php
└── README.md
```

### Local Development with Local by Flywheel (WSL)

**IMPORTANT**: If you're developing in WSL and testing with Local by Flywheel on Windows, the working directory and the WordPress plugins directory are separate locations.

**Development Workflow:**

1. Make changes to files in your WSL development directory (e.g., `/home/pa/trimontium-website-login/`)
2. Sync changes to the Local WordPress installation using rsync:
   ```bash
   rsync -av --delete --exclude='.git' --exclude='.gitignore' \
     /home/pa/trimontium-website-login/ \
     "/mnt/c/Users/YOUR_USERNAME/Local Sites/trimontium-test-local/app/public/wp-content/plugins/trimontium-website-login/"
   ```
3. Hard refresh your browser (Ctrl+F5 or Cmd+Shift+R) to see changes

**Note**: Changes made in the WSL directory will NOT automatically appear in your Local WordPress site. You must sync the files using the command above each time you want to test your changes.

### Classes

- **Trimontium_Website_Login**: Main plugin class
- **TPA_Roles**: Role and capability management
- **TPA_Auth**: Authentication and access control
- **TPA_Dashboard**: Dashboard and widget management
- **TPA_API**: API integration for Azure and Databricks
- **TPA_Admin**: Admin interface

## Support

For issues and feature requests, please contact your administrator.

## License

GPL v2 or later

## Changelog

### 1.0.0
- Initial release
- Azure integration
- Databricks integration
- Role-based access control
- Dashboard system with widgets
- Admin interface
