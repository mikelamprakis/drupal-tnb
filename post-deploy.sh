#!/bin/bash
# Enable required modules
cd /var/www/html
./vendor/bin/drush en -y jsonapi jsonapi_extras serialization basic_auth rest
# Set permissions
./vendor/bin/drush role-add-perm anonymous 'access jsonapi resource list'
./vendor/bin/drush role-add-perm anonymous 'access jsonapi resources'
# Clear cache
./vendor/bin/drush cr
echo "JSON:API modules enabled and configured successfully!" 