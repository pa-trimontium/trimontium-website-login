# Local WordPress Setup Guide

## Step 1: Install the Plugin

### Option A: Copy Plugin to WordPress (Recommended)

1. **Locate your Local by Flywheel site folder**:
   - Open Local by Flywheel
   - Right-click your site → "Reveal in Finder/Explorer"
   - Navigate to: `app/public/wp-content/plugins/`

2. **Copy the plugin folder**:
   ```bash
   # From this directory, copy the entire folder to your WordPress plugins directory
   # Example path (adjust to your site name):
   cp -r /home/pa/trimontium-wp-private-dashboards /path/to/your-site/app/public/wp-content/plugins/
   ```

### Option B: Create Symbolic Link (For Development)

This allows you to edit files in your current location and see changes immediately:

```bash
# Create a symlink instead of copying
ln -s /home/pa/trimontium-wp-private-dashboards /path/to/your-site/app/public/wp-content/plugins/trimontium-wp-private-dashboards
```

## Step 2: Activate the Plugin

1. **Access WordPress Admin**:
   - Open your Local by Flywheel site
   - Click "Admin" button or go to `http://yoursite.local/wp-admin`

2. **Activate the plugin**:
   - Go to **Plugins** → **Installed Plugins**
   - Find "Trimontium WP Private Dashboards"
   - Click **Activate**

3. **Verify activation**:
   - You should see a new menu item "Private Area" in the admin sidebar
   - The plugin creates a custom role called "Private Area User"

## Step 3: Configure Settings

### General Settings

1. Go to **Private Area** → **Settings**
2. Configure:
   - **Main Dashboard Page**: Select which page users see after login
   - **Enable API Logging**: Check to log API requests for debugging
   - **Cache Duration**: Set to 300 seconds (5 minutes) for testing

### API Credentials (For Testing)

Since you're testing locally, you have two options:

#### Option 1: Use Mock/Test Data (Recommended for Initial Testing)

For now, skip the API credentials. The plugin will show errors, but you can still:
- Create dashboard pages
- Test the user role system
- Test the authentication flow
- See the widget UI (just without real data)

#### Option 2: Use Real Azure/Databricks Credentials

**Azure Setup**:
1. Go to **Private Area** → **API Credentials** → **Azure tab**
2. Enter your Azure credentials:
   - **Tenant ID**: Your Azure AD tenant ID
   - **Client ID**: Application (client) ID
   - **Client Secret**: Client secret value
   - **Subscription ID**: Your Azure subscription ID
3. Click **Save Changes**
4. Click **Test Azure Connection** to verify

**Databricks Setup**:
1. Go to **Private Area** → **API Credentials** → **Databricks tab**
2. Enter your credentials:
   - **Workspace URL**: `https://adb-xxxxx.azuredatabricks.net`
   - **Access Token**: Your personal access token
3. Click **Save Changes**
4. Click **Test Databricks Connection** to verify

## Step 4: Create a Test User

1. **Create a new user**:
   - Go to **Users** → **Add New**
   - Username: `testuser`
   - Email: `test@example.com`
   - Password: Set a password
   - Role: Select **Private Area User**
   - Click **Add New User**

2. **Or grant access to existing user**:
   - Go to **Users** → **All Users**
   - Select user(s)
   - From **Bulk Actions** → select **Grant Private Area Access**
   - Click **Apply**

## Step 5: Create Dashboard Pages

### Method 1: Using Dashboard Post Type

1. **Create a new dashboard**:
   - Go to **Private Area** → **Add New**
   - Title: "Test Azure Dashboard"
   - Add content in the editor

2. **Configure Dashboard Settings** (right sidebar):
   - ✓ Check "Require authentication to view"
   - **Dashboard Type**: Select "Azure" or "Databricks"
   - **Auto-refresh interval**: 60 (seconds)

3. **Add widgets using shortcodes**:
   ```
   [tpa_azure_widget
       type="metrics"
       resource_id="/subscriptions/xxx/resourceGroups/xxx"
       metric="Percentage CPU"
       title="CPU Usage"]
   ```

4. Click **Publish**

### Method 2: Using Regular WordPress Pages

1. **Create a new page**:
   - Go to **Pages** → **Add New**
   - Title: "Private Dashboard"

2. **Add a shortcode**:
   ```
   [tpa_databricks_widget
       type="jobs"
       workspace="https://adb-xxxxx.azuredatabricks.net"
       limit="10"
       title="Recent Jobs"]
   ```

3. **Mark as private** (if using custom post type meta):
   - In Dashboard Settings, check "Require authentication to view"

4. Click **Publish**

## Step 6: Test the Setup

### Test Authentication Flow

1. **Open an incognito/private browser window**
2. **Navigate to your dashboard page**:
   - Go to the URL of your published dashboard
   - Example: `http://yoursite.local/test-azure-dashboard/`

3. **Expected behavior**:
   - You should be redirected to login page
   - After login with test user, redirected back to dashboard
   - Dashboard content should be visible

### Test Access Control

1. **Create a regular user** (without Private Area User role):
   - Go to **Users** → **Add New**
   - Set role to **Subscriber** (not Private Area User)

2. **Try to access dashboard**:
   - Login as this user
   - Navigate to private dashboard
   - Expected: "Access Denied" message

### Test Widgets (Without API Credentials)

1. **View dashboard page**
2. **Expected behavior**:
   - Widget headers should display
   - Loading spinner appears
   - Error message shows (if no API credentials)
   - This is normal for testing!

### Test Widgets (With API Credentials)

1. **After configuring API credentials**
2. **View dashboard page**
3. **Expected behavior**:
   - Loading spinner appears
   - Real data loads from Azure/Databricks
   - Charts/tables display

## Step 7: Admin Interface Tour

### Main Dashboard
- Go to **Private Area**
- View quick stats (authorized users, dashboards)
- Test connection buttons

### Users Management
- Go to **Private Area** → **Users**
- See list of users with private area access
- Check which users have Azure/Databricks access

### API Logs
- Go to **Private Area** → **API Logs**
- View API request history
- Check for errors or issues

## Common Issues & Solutions

### Issue: "Private Area" menu not showing
**Solution**:
- Deactivate and reactivate the plugin
- Clear WordPress cache

### Issue: "Access Denied" for admin users
**Solution**:
- Administrators automatically get all capabilities
- Check if you're logged in as admin
- Try deactivating and reactivating plugin

### Issue: Widgets show error messages
**Solution**:
- This is expected without API credentials
- Configure credentials in **Private Area** → **API Credentials**
- Test connections using test buttons

### Issue: Dashboard page not found
**Solution**:
- Go to **Settings** → **Permalinks**
- Click **Save Changes** (flushes rewrite rules)

### Issue: Styles not loading
**Solution**:
- Hard refresh browser (Ctrl+F5 or Cmd+Shift+R)
- Check if page slug starts with "private-" or has private meta set

## Quick Test Checklist

- [ ] Plugin activated successfully
- [ ] "Private Area" menu appears in admin
- [ ] Settings page accessible
- [ ] Test user created with Private Area User role
- [ ] Dashboard page created
- [ ] Dashboard requires authentication (tested in incognito)
- [ ] Authorized user can view dashboard
- [ ] Unauthorized user gets "Access Denied"
- [ ] Widgets display (even with errors is OK for now)
- [ ] Admin interface accessible
- [ ] API logs recording (if enabled)

## Next Steps

1. **Add real API credentials** to see live data
2. **Create multiple dashboards** for different purposes
3. **Customize templates** (copy to theme folder)
4. **Add custom CSS** for branding
5. **Test auto-refresh** functionality
6. **Configure cache settings** for performance

## Development Tips

### Enable Debug Mode

Add to `wp-config.php`:
```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
```

Check logs at: `wp-content/debug.log`

### Clear Caches

1. **Plugin cache**:
   - Go to **Private Area** → **Settings**
   - Click cache clear buttons

2. **WordPress cache**:
   - Install "WP Super Cache" or similar
   - Clear from admin bar

### Database Tables

The plugin creates:
- `wp_tpa_api_logs` - API request logs

View with phpMyAdmin or Adminer (available in Local by Flywheel)

## Support

For issues or questions:
- Check the main [README.md](README.md)
- Review API logs in admin
- Check WordPress debug.log
- Verify API credentials are correct
