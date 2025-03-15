#!/bin/bash

# Script to set up remote database connection for local Drupal
# This will modify your DDEV configuration to use the remote Render.com database

echo "Setting up remote database connection for Drupal..."

# Check if we're in the right directory
if [ ! -d ".ddev" ]; then
  echo "Error: This script must be run from the project root directory"
  echo "Current directory: $(pwd)"
  exit 1
fi

# Prompt for Render.com database URL
echo "Enter your Render.com PostgreSQL DATABASE_URL:"
echo "(Format: postgres://username:password@host:port/database)"
read -p "> " DATABASE_URL

if [ -z "$DATABASE_URL" ]; then
  echo "Error: No database URL provided"
  exit 1
fi

# Update DDEV config.yaml
echo "Updating .ddev/config.yaml..."
cat > .ddev/config.custom.yaml << EOF
web_environment:
  - RENDER_DATABASE_URL=${DATABASE_URL}
webimage_extra_packages:
  - postgresql-client
  - php-pgsql
hooks:
  post-start:
    - exec: composer install
EOF

echo ".ddev/config.custom.yaml created with your database credentials"

# Make sure settings.local.php exists
if [ ! -f "web/sites/default/settings.local.php" ]; then
  echo "Creating web/sites/default/settings.local.php..."
  cp web/sites/example.settings.local.php web/sites/default/settings.local.php 2>/dev/null || true
fi

# Update settings.local.php
echo "Updating web/sites/default/settings.local.php..."
cat >> web/sites/default/settings.local.php << EOF

/**
 * Remote database connection settings
 */
\$render_db_url = getenv('RENDER_DATABASE_URL');

if (!empty(\$render_db_url)) {
  // Parse the database URL
  \$db_url = parse_url(\$render_db_url);
  
  // Override the default database connection
  \$databases['default']['default'] = [
    'database' => ltrim(\$db_url['path'], '/'),
    'username' => \$db_url['user'],
    'password' => \$db_url['pass'],
    'host' => \$db_url['host'],
    'port' => \$db_url['port'] ?? 5432,
    'driver' => 'pgsql',
    'prefix' => '',
  ];
  
  // Set PostgreSQL-specific settings
  \$settings['pgsql_utf8mb4'] = TRUE;
}
EOF

echo "settings.local.php updated with remote database configuration"

# Restart DDEV
echo "Restarting DDEV..."
ddev restart

echo ""
echo "Setup complete! To test your connection:"
echo "1. Visit https://drupal-tnb.ddev.site/db-connection-test.php"
echo "2. Or run: ddev exec php web/db-connection-test.php"
echo ""
echo "If you encounter issues connecting to Render.com database:"
echo "- Make sure your database is accessible from external IPs (check Render dashboard)"
echo "- Consider setting up an SSH tunnel or VPN if required"
echo "- Check database credentials and URL format" 