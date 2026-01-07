# Project-Specific Instructions for Claude

## Local Development with Local by Flywheel (WSL)

**IMPORTANT**: If you're developing in WSL and testing with Local by Flywheel on Windows, the working directory and the WordPress plugins directory are separate locations.

**Development Workflow:**

1. Make changes to files in your WSL development directory (e.g., `/home/pa/trimontium-wp-private-dashboards/`)
2. Sync changes to the Local WordPress installation using rsync:
   ```bash
   rsync -av --delete --exclude='.git' --exclude='.gitignore' --exclude='.synced' \
     /home/pa/trimontium-wp-private-dashboards/ \
     "/mnt/c/Users/Gandalf/Local Sites/trimontium-test-local/app/public/wp-content/plugins/trimontium-wp-private-dashboards/" && \
   touch "/mnt/c/Users/Gandalf/Local Sites/trimontium-test-local/app/public/wp-content/plugins/trimontium-wp-private-dashboards/.synced"
   ```
3. Hard refresh your browser (Ctrl+F5 or Cmd+Shift+R) to see changes

**Note**: Changes made in the WSL directory will NOT automatically appear in your Local WordPress site. You must sync the files using the command above each time you want to test your changes.

If told to 'Sync' it means to do this process.

When making changes to this plugin project, always test it using the Local WP site before testing on the live deployment.

## Deployment

### Creating Deployment Package

A .zip version of the plugin is created for uploading to the live WordPress site.

**Naming Convention**: `trimontium-wp-private-dashboards-YYYYMMDD-HHMM.zip` (includes timestamp)

**Note**: The .zip file is excluded from version control (see .gitignore). When  instructed to create the deployment .zip package inside the project folder to keep all the files together.

If told to 'Prod' it means to do this process.

## Test and Live website environments

### PHP and WordPress versions

The PHP and WP version of the Local test site is:

PHP Version 8.2.27
WordPress Version 6.9

The live site is:

PHP version	8.2.29
WordPress Version 6.9

### Filesize limits

The local test site can handle larger files from DB, but the live site cannot. The current workaround is to limit the number of leads being loaded. Possible longer term solutions are pagination, server-side filtering, lazy loading or increasing PHP limits.