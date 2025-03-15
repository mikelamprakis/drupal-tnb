<?php

/**
 * @file
 * Render.com specific settings file.
 */

// Enable detailed error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Parse the DATABASE_URL environment variable
if (getenv('DATABASE_URL')) {
  $db_url = parse_url(getenv('DATABASE_URL'));
  
  $databases['default']['default'] = [
    'database' => ltrim($db_url['path'], '/'),
    'username' => $db_url['user'],
    'password' => $db_url['pass'],
    'host' => $db_url['host'],
    'port' => $db_url['port'] ?? 5432,
    'driver' => 'pgsql',
    'prefix' => '',
    'sslmode' => 'require',
  ];
  
  // Set PostgreSQL specific settings
  $settings['pgsql_utf8mb4'] = TRUE;
}

// Set the trusted host patterns from environment variable
if (getenv('TRUSTED_HOST_PATTERNS')) {
  $trusted_host_patterns = explode(',', getenv('TRUSTED_HOST_PATTERNS'));
  $settings['trusted_host_patterns'] = $trusted_host_patterns;
}

// Increase memory limit if needed
ini_set('memory_limit', '256M');

// Disable CSS and JS aggregation for debugging if needed
// $config['system.performance']['css']['preprocess'] = FALSE;
// $config['system.performance']['js']['preprocess'] = FALSE;

// Enable verbose error logging in production temporarily if needed
$config['system.logging']['error_level'] = 'verbose';

// Hash salt - ensure this is set for every environment
if (getenv('HASH_SALT')) {
  $settings['hash_salt'] = getenv('HASH_SALT');
} else {
  $settings['hash_salt'] = 'this-is-a-placeholder-salt-replace-in-production-' . md5(__FILE__);
}

// Set file paths
$settings['file_public_path'] = 'sites/default/files';
$settings['file_private_path'] = '../private';
$settings['file_temp_path'] = '/tmp';

// Ensure file system is not hardened in production - use with caution
$settings['skip_permissions_hardening'] = TRUE; 