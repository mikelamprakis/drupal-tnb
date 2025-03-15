<?php

/**
 * @file
 * Settings to override DDEV's database settings with remote Render.com database.
 */

// This file should be included after settings.ddev.php to override its settings

// Get the Render.com database URL
$render_db_url = getenv('RENDER_DATABASE_URL');

if (!empty($render_db_url)) {
  // Clear any existing database settings to avoid conflicts
  $databases = [];
  
  // Parse the database URL
  $db_url = parse_url($render_db_url);
  
  // Extract database name from path correctly
  $database = ltrim($db_url['path'], '/');
  
  // Define the database connection
  $databases['default']['default'] = [
    'database' => $database,
    'username' => $db_url['user'],
    'password' => $db_url['pass'],
    'host' => $db_url['host'],
    'port' => $db_url['port'] ?? '5432',
    'driver' => 'pgsql',
    'prefix' => '',
    'namespace' => 'Drupal\\Core\\Database\\Driver\\pgsql',
    'autoload' => 'core/modules/pgsql/src/Driver/Database/pgsql/',
    // Add SSL requirements for Render
    'pdo' => [
      PDO::ATTR_TIMEOUT => 5,
    ],
    'sslmode' => 'require',
  ];
  
  // Logging for debugging
  error_log("Successfully overrode database connection to use remote Render PostgreSQL database");
  error_log("Remote database connection enabled with SSL: " . $db_url['host']);
} 