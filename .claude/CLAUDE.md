# Project-Specific Instructions for Claude

## Local Development with Local by Flywheel (WSL)

**IMPORTANT**: If you're developing in WSL and testing with Local by Flywheel on Windows, the working directory and the WordPress plugins directory are separate locations.

**Development Workflow:**

1. Make changes to files in your WSL development directory (e.g., `/home/pa/trimontium-website-login/`)
2. Sync changes to the Local WordPress installation using rsync:
   ```bash
   rsync -av --delete --exclude='.git' --exclude='.gitignore' --exclude='.synced' \
     /home/pa/trimontium-website-login/ \
     "/mnt/c/Users/Gandalf/Local Sites/trimontium-test-local/app/public/wp-content/plugins/trimontium-website-login/" && \
   touch "/mnt/c/Users/Gandalf/Local Sites/trimontium-test-local/app/public/wp-content/plugins/trimontium-website-login/.synced"
   ```
3. Hard refresh your browser (Ctrl+F5 or Cmd+Shift+R) to see changes

**Note**: Changes made in the WSL directory will NOT automatically appear in your Local WordPress site. You must sync the files using the command above each time you want to test your changes.

If told to 'Sync' it means to do this process.

## Deployment

### Creating Deployment Package

A .zip version of the plugin is created for uploading to the live WordPress site.

**Naming Convention**: `trimontium-website-login-YYYYMMDD-HHMM.zip` (includes timestamp)

**Note**: The .zip file is excluded from version control (see .gitignore). Create or update the deployment package when changes need to be deployed to production.

If told to 'Prod' it means to do this process.
