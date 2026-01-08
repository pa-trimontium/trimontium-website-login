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

**CRITICAL - MUST PRESERVE DIRECTORY STRUCTURE:**

WordPress plugins MUST maintain their directory structure in the zip file. The plugin expects files in specific directories:
- `includes/class-tpa-*.php`
- `admin/class-tpa-admin.php`
- `templates/*.php`
- `assets/css/*.css`
- `assets/js/*.js`

**DO NOT USE** `python3 -m zipfile -c` as it flattens the directory structure, causing fatal errors on activation.

**CORRECT METHOD** - Use this Python script to create the deployment package:

```python
cd /home/pa && python3 << 'EOF'
import os
import zipfile
from datetime import datetime

timestamp = datetime.now().strftime('%Y%m%d-%H%M')
zip_filename = f'trimontium-wp-private-dashboards-{timestamp}.zip'
zip_path = f'/home/pa/trimontium-wp-private-dashboards/{zip_filename}'

exclude_patterns = ['.git', '.gitignore', '.claude', '.synced', '.zip', '.tar.gz']

def should_exclude(path):
    for pattern in exclude_patterns:
        if pattern in path:
            return True
    return False

with zipfile.ZipFile(zip_path, 'w', zipfile.ZIP_DEFLATED) as zipf:
    base_dir = '/home/pa/trimontium-wp-private-dashboards'

    for root, dirs, files in os.walk(base_dir):
        dirs[:] = [d for d in dirs if not should_exclude(d)]

        for file in files:
            file_path = os.path.join(root, file)
            if should_exclude(file_path):
                continue
            arcname = os.path.relpath(file_path, base_dir)
            zipf.write(file_path, arcname)

print(f'Created: {zip_filename}')
EOF
```

This script preserves the directory structure by using `os.walk()` and maintaining relative paths with `os.path.relpath()`.

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