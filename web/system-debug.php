<?php

/**
 * System diagnostic script for Drupal.
 * This script checks various system configurations that might be causing issues.
 */

// Display all errors
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>Drupal System Diagnostics</h1>";

// Check PHP version
echo "<h2>PHP Environment</h2>";
echo "<p>PHP Version: " . phpversion() . "</p>";

// Check PHP memory limit
echo "<p>Memory Limit: " . ini_get('memory_limit') . "</p>";
echo "<p>Max Execution Time: " . ini_get('max_execution_time') . " seconds</p>";

// Check loaded PHP extensions
echo "<h2>PHP Extensions</h2>";
$required_extensions = [
  'gd', 'pdo', 'pdo_pgsql', 'xml', 'json', 'mbstring', 'opcache', 'curl'
];
echo "<ul>";
foreach ($required_extensions as $ext) {
  $loaded = extension_loaded($ext);
  echo "<li>" . $ext . ": " . ($loaded ? "✅ Loaded" : "❌ Not Loaded") . "</li>";
}
echo "</ul>";

// Check file permissions
echo "<h2>File Permissions</h2>";
$paths_to_check = [
  'sites/default',
  'sites/default/files',
  'sites/default/files/css',
  'sites/default/files/js',
  'sites/default/settings.php',
];

echo "<ul>";
foreach ($paths_to_check as $path) {
  if (file_exists($path)) {
    $perms = substr(sprintf('%o', fileperms($path)), -4);
    $writable = is_writable($path);
    echo "<li>" . $path . ": Permissions: " . $perms . " " . 
         ($writable ? "✅ Writable" : "❌ Not Writable") . "</li>";
  } else {
    echo "<li>" . $path . ": ❌ Does not exist</li>";
  }
}
echo "</ul>";

// Check environment variables
echo "<h2>Environment Variables</h2>";
$important_vars = [
  'DATABASE_URL',
  'RENDER_DATABASE_URL',
  'HASH_SALT',
  'TRUSTED_HOST_PATTERNS',
];

echo "<ul>";
foreach ($important_vars as $var) {
  echo "<li>" . $var . ": " . (getenv($var) ? "✅ Set" : "❌ Not Set") . "</li>";
}
echo "</ul>";

// Check database connection (simplified from db-debug.php)
echo "<h2>Database Connection</h2>";
try {
  // Include Drupal settings to get database config
  require_once 'sites/default/settings.php';
  
  if (isset($databases) && isset($databases['default']['default'])) {
    $db_config = $databases['default']['default'];
    echo "<p>Database type: " . $db_config['driver'] . "</p>";
    echo "<p>Connection status: ";
    
    if ($db_config['driver'] === 'pgsql') {
      $dsn = "pgsql:host={$db_config['host']};port={$db_config['port']};dbname={$db_config['database']}";
      
      if (isset($db_config['sslmode']) && $db_config['sslmode'] === 'require') {
        $dsn .= ";sslmode=require";
      }
      
      $pdo = new PDO($dsn, $db_config['username'], $db_config['password']);
      $stmt = $pdo->query("SELECT 1");
      echo "✅ Connected successfully</p>";
    } else {
      echo "❓ Not configured for PostgreSQL</p>";
    }
  } else {
    echo "<p>❌ No database configuration found in settings.php</p>";
  }
} catch (Exception $e) {
  echo "<p>❌ Database connection error: " . htmlspecialchars($e->getMessage()) . "</p>";
}

// Check trusted host patterns
echo "<h2>Trusted Host Patterns</h2>";
if (isset($settings) && isset($settings['trusted_host_patterns'])) {
  echo "<p>✅ Trusted host patterns are configured:</p>";
  echo "<ul>";
  foreach ($settings['trusted_host_patterns'] as $pattern) {
    echo "<li>" . htmlspecialchars($pattern) . "</li>";
  }
  echo "</ul>";
} else {
  echo "<p>❌ No trusted host patterns configured</p>";
}

// Server information
echo "<h2>Server Information</h2>";
echo "<p>Server Software: " . $_SERVER['SERVER_SOFTWARE'] . "</p>";
echo "<p>Server Name: " . $_SERVER['SERVER_NAME'] . "</p>";
echo "<p>Request URI: " . $_SERVER['REQUEST_URI'] . "</p>";

// Check Drupal core by looking for bootstrap.inc
echo "<h2>Drupal Files</h2>";
$core_files = [
  'core/lib/Drupal.php',
  'core/includes/bootstrap.inc',
  'index.php',
  'autoload.php',
];

echo "<ul>";
foreach ($core_files as $file) {
  echo "<li>" . $file . ": " . (file_exists($file) ? "✅ Exists" : "❌ Missing") . "</li>";
}
echo "</ul>";

echo "<p>This diagnostic information can help identify issues with your Drupal installation.</p>"; 