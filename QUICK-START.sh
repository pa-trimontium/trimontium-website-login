#!/bin/bash

# Quick Start Script for Trimontium Website Login Plugin
# This script helps you install the plugin to your Local by Flywheel WordPress site

echo "=== Trimontium Website Login - Quick Install ==="
echo ""

# Check if running from plugin directory
if [ ! -f "trimontium-website-login.php" ]; then
    echo "Error: Please run this script from the plugin directory"
    exit 1
fi

# Get WordPress path
echo "Enter the path to your WordPress installation:"
echo "Example: ~/Local Sites/mysite/app/public"
read -p "WordPress path: " WP_PATH

# Validate path
if [ ! -d "$WP_PATH/wp-content/plugins" ]; then
    echo "Error: Invalid WordPress path. wp-content/plugins not found."
    exit 1
fi

# Ask installation method
echo ""
echo "Choose installation method:"
echo "1) Copy files (standalone installation)"
echo "2) Create symlink (for development - changes reflect immediately)"
read -p "Choice (1/2): " METHOD

PLUGIN_PATH="$WP_PATH/wp-content/plugins/trimontium-website-login"

if [ "$METHOD" = "1" ]; then
    # Copy method
    echo ""
    echo "Copying plugin files..."
    cp -r "$(pwd)" "$PLUGIN_PATH"
    echo "✓ Plugin copied to: $PLUGIN_PATH"
elif [ "$METHOD" = "2" ]; then
    # Symlink method
    echo ""
    echo "Creating symbolic link..."
    ln -s "$(pwd)" "$PLUGIN_PATH"
    echo "✓ Symlink created: $PLUGIN_PATH -> $(pwd)"
else
    echo "Invalid choice"
    exit 1
fi

echo ""
echo "=== Installation Complete! ==="
echo ""
echo "Next steps:"
echo "1. Go to your WordPress admin: http://yoursite.local/wp-admin"
echo "2. Navigate to Plugins → Installed Plugins"
echo "3. Find 'Trimontium Website Login' and click Activate"
echo "4. Configure settings in Private Area menu"
echo ""
echo "See SETUP-GUIDE.md for detailed instructions"
