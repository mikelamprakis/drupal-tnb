FROM php:8.3-apache

# Install required dependencies and PHP extensions
RUN apt-get update && apt-get install -y \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    libpq-dev \
    libzip-dev \
    postgresql-client \
    unzip \
    git \
    vim \
    libicu-dev \
    libxml2-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd pdo_pgsql pgsql zip opcache intl xml soap

# Enable Apache modules
RUN a2enmod rewrite

# Configure PHP for development
RUN mv "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini" \
    && sed -i 's/upload_max_filesize = 2M/upload_max_filesize = 32M/' "$PHP_INI_DIR/php.ini" \
    && sed -i 's/post_max_size = 8M/post_max_size = 64M/' "$PHP_INI_DIR/php.ini" \
    && sed -i 's/memory_limit = 128M/memory_limit = 512M/' "$PHP_INI_DIR/php.ini" \
    && sed -i 's/;display_errors = Off/display_errors = On/' "$PHP_INI_DIR/php.ini" \
    && sed -i 's/display_errors = Off/display_errors = On/' "$PHP_INI_DIR/php.ini" \
    && sed -i 's/display_startup_errors = Off/display_startup_errors = On/' "$PHP_INI_DIR/php.ini" \
    && sed -i 's/error_reporting = E_ALL & ~E_DEPRECATED & ~E_STRICT/error_reporting = E_ALL/' "$PHP_INI_DIR/php.ini"

# Set up document root
ENV APACHE_DOCUMENT_ROOT /var/www/html/web
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}/!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set up working directory
WORKDIR /var/www/html

# Copy project files
COPY . .

# Copy Apache configuration
COPY apache.conf /etc/apache2/sites-available/000-default.conf

# Create a diagnostic page
RUN echo '<?php phpinfo(); ?>' > /var/www/html/web/info.php

# Create entrypoint script
RUN echo '#!/bin/bash\n\
echo "Setting Apache port to $PORT..."\n\
sed -i "s/Listen 80/Listen ${PORT:-80}/" /etc/apache2/ports.conf\n\
sed -i "s/:80/:${PORT:-80}/" /etc/apache2/sites-available/*.conf\n\
\n\
echo "Checking for web directory..."\n\
if [ ! -d "/var/www/html/web" ]; then\n\
  echo "ERROR: web directory not found! Creating it..."\n\
  mkdir -p /var/www/html/web\n\
  echo "<?php phpinfo(); ?>" > /var/www/html/web/index.php\n\
  echo "Directory created with basic PHP info page!"\n\
fi\n\
\n\
echo "Setting up proper permissions..."\n\
chown -R www-data:www-data /var/www/html\n\
find /var/www/html/web -type d -exec chmod 755 {} \\;\n\
find /var/www/html/web -type f -exec chmod 644 {} \\;\n\
\n\
echo "Ensuring sites/default/files directory exists and is writable..."\n\
mkdir -p /var/www/html/web/sites/default/files\n\
chmod -R 777 /var/www/html/web/sites/default/files\n\
chown -R www-data:www-data /var/www/html/web/sites/default/files\n\
\n\
echo "Checking for settings.php..."\n\
if [ -f "/var/www/html/web/sites/default/settings.php" ]; then\n\
  echo "settings.php exists, making it writable..."\n\
  chmod 666 /var/www/html/web/sites/default/settings.php\n\
else\n\
  echo "WARNING: settings.php not found! Creating default one..."\n\
  cp /var/www/html/web/sites/default/default.settings.php /var/www/html/web/sites/default/settings.php 2>/dev/null || echo "ERROR: Could not create settings.php!"\n\
  chmod 666 /var/www/html/web/sites/default/settings.php 2>/dev/null\n\
fi\n\
\n\
echo "Installing dependencies if composer.json exists..."\n\
if [ -f "/var/www/html/composer.json" ]; then\n\
  echo "composer.json found, running composer install..."\n\
  composer install --prefer-dist --no-dev --optimize-autoloader --no-interaction --ignore-platform-reqs\n\
else\n\
  echo "WARNING: composer.json not found! Skipping dependency installation."\n\
fi\n\
\n\
echo "Listing web directory contents:"\n\
ls -la /var/www/html/web\n\
\n\
echo "Starting Apache..."\n\
apache2-foreground' > /usr/local/bin/entrypoint.sh \
    && chmod +x /usr/local/bin/entrypoint.sh

# We will run the entrypoint script instead of immediately starting Apache
CMD ["/usr/local/bin/entrypoint.sh"] 