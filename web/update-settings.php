<?php

/**
 * Update Settings.php Script
 * This script modifies settings.php to include the PostgreSQL fix
 */

// Enable error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h1>Update Settings.php</h1>";

// Define constants
$app_root = dirname(__FILE__);
$site_path = 'sites/default';

// Define paths
$settings_file = $app_root . '/' . $site_path . '/settings.php';
$pgsql_fix_file = $app_root . '/' . $site_path . '/pgsql-fix.php';

// Check if settings.php exists
if (!file_exists($settings_file)) {
  die("<p>❌ settings.php not found at: $settings_file</p>");
}

// Check if pgsql-fix.php exists
if (!file_exists($pgsql_fix_file)) {
  die("<p>❌ pgsql-fix.php not found at: $pgsql_fix_file</p>");
}

// Read settings.php
$settings_content = file_get_contents($settings_file);

// Check if pgsql-fix.php is already included
if (strpos($settings_content, 'pgsql-fix.php') !== false) {
  echo "<p>✅ pgsql-fix.php is already included in settings.php.</p>";
} else {
  echo "<p>Adding pgsql-fix.php include to settings.php...</p>";
  
  // Add include at the end of settings.php
  $include_code = "\n\n// Include PostgreSQL SSL fix\n";
  $include_code .= "if (file_exists(\$app_root . '/' . \$site_path . '/pgsql-fix.php')) {\n";
  $include_code .= "  include \$app_root . '/' . \$site_path . '/pgsql-fix.php';\n";
  $include_code .= "}\n";
  
  // Write updated content back to settings.php
  if (file_put_contents($settings_file, $settings_content . $include_code)) {
    echo "<p>✅ Successfully updated settings.php to include pgsql-fix.php!</p>";
  } else {
    echo "<p>❌ Failed to update settings.php.</p>";
  }
}

// Test database connection
echo "<h2>Testing Database Connection</h2>";

// Include settings.php to get database configuration
try {
  // We need to define these variables before including settings.php
  $app_root = dirname(__FILE__);
  $site_path = 'sites/default';
  
  // Define DRUPAL_ROOT if not already defined
  if (!defined('DRUPAL_ROOT')) {
    define('DRUPAL_ROOT', $app_root);
  }
  
  // Include settings.php
  include $settings_file;
  
  // Check if database configuration exists
  if (isset($databases['default']['default'])) {
    echo "<p>Found database configuration:</p>";
    echo "<ul>";
    echo "<li>Driver: " . $databases['default']['default']['driver'] . "</li>";
    echo "<li>Database: " . $databases['default']['default']['database'] . "</li>";
    echo "<li>Host: " . $databases['default']['default']['host'] . "</li>";
    
    // Check for SSL configuration
    if (isset($databases['default']['default']['sslmode'])) {
      echo "<li>SSL Mode: " . $databases['default']['default']['sslmode'] . "</li>";
    } else {
      echo "<li>SSL Mode: Not set</li>";
    }
    echo "</ul>";
    
    // Try to connect to the database
    echo "<p>Attempting database connection...</p>";
    
    try {
      // Extract connection details (without exposing credentials in output)
      $driver = $databases['default']['default']['driver'];
      $host = $databases['default']['default']['host'];
      $database = $databases['default']['default']['database'];
      $username = $databases['default']['default']['username'];
      $password = $databases['default']['default']['password'];
      
      // Create the DSN
      $dsn = $driver . ':host=' . $host . ';dbname=' . $database;
      
      // Set PDO options
      $options = [];
      if (isset($databases['default']['default']['pdo'])) {
        $options = $databases['default']['default']['pdo'];
      }
      
      // Create PDO connection
      $pdo = new PDO($dsn, $username, $password, $options);
      $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      
      echo "<p>✅ Successfully connected to the database!</p>";
      
      // Check essential tables
      $essential_tables = ['config', 'users', 'node'];
      echo "<p>Checking essential tables:</p>";
      echo "<ul>";
      
      foreach ($essential_tables as $table) {
        try {
          $stmt = $pdo->query("SELECT 1 FROM " . $table . " LIMIT 1");
          if ($stmt !== false) {
            echo "<li>" . $table . ": ✅ Exists</li>";
          } else {
            echo "<li>" . $table . ": ❌ Error checking</li>";
          }
        } catch (PDOException $e) {
          echo "<li>" . $table . ": ❌ Error - " . $e->getMessage() . "</li>";
        }
      }
      
      echo "</ul>";
      
    } catch (PDOException $e) {
      echo "<p>❌ Database connection failed: " . $e->getMessage() . "</p>";
    }
  } else {
    echo "<p>❌ No database configuration found in settings.php.</p>";
  }
} catch (Exception $e) {
  echo "<p>❌ Error including settings.php: " . $e->getMessage() . "</p>";
}

// Next steps
echo "<h2>Next Steps</h2>";
echo "<p>Now that settings.php has been updated to include the PostgreSQL fix, try accessing your site:</p>";
echo "<ul>";
echo "<li><a href='/index-fix.php'>Access your site via index-fix.php</a></li>";
echo "<li><a href='/'>Access your site's homepage</a></li>";
echo "</ul>"; 