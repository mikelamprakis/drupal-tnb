<?php

/**
 * PostgreSQL SSL Connection Fix for Drupal on Render
 * This file should be included by settings.php to properly configure PostgreSQL SSL
 */

// Only run if we have database configuration
if (isset($databases['default']['default'])) {
  // Check if this is PostgreSQL
  if ($databases['default']['default']['driver'] === 'pgsql') {
    // Add SSL configuration for PostgreSQL
    $databases['default']['default']['pdo'][PDO::ATTR_TIMEOUT] = 5;
    $databases['default']['default']['pdo'][PDO::ATTR_PERSISTENT] = false;
    
    // Required for Render PostgreSQL connections
    $databases['default']['default']['sslmode'] = 'require';
    
    // Make sure we're not using MySQL-specific options for PostgreSQL
    if (isset($databases['default']['default']['pdo'][PDO::MYSQL_ATTR_SSL_CA])) {
      unset($databases['default']['default']['pdo'][PDO::MYSQL_ATTR_SSL_CA]);
    }
    
    // Log that this fix was applied
    error_log('PostgreSQL SSL configuration applied via pgsql-fix.php');
  }
} 