<?php

/**
 * Site repair script for Drupal
 * Creates necessary directories and updates settings
 */

// Display all errors
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>Drupal Site Repair</h1>";

// Create necessary directories
$dirs_to_create = [
  'sites/default/files/css',
  'sites/default/files/js',
  'sites/default/files/php',
  'sites/default/files/styles',
  'sites/default/files/translations',
];

foreach ($dirs_to_create as $dir) {
  if (!file_exists($dir)) {
    echo "<p>Creating directory: $dir</p>";
    mkdir($dir, 0777, true);
    if (file_exists($dir)) {
      echo "<p>✅ Successfully created $dir</p>";
    } else {
      echo "<p>❌ Failed to create $dir</p>";
    }
  } else {
    echo "<p>Directory already exists: $dir</p>";
  }
}

// Set directory permissions
$dirs_to_chmod = [
  'sites/default/files',
  'sites/default/files/css',
  'sites/default/files/js',
  'sites/default/files/php',
  'sites/default/files/styles',
  'sites/default/files/translations',
];

foreach ($dirs_to_chmod as $dir) {
  if (file_exists($dir)) {
    echo "<p>Setting permissions for $dir</p>";
    chmod($dir, 0777);
    $perms = substr(sprintf('%o', fileperms($dir)), -4);
    echo "<p>New permissions: $perms</p>";
  }
}

// Trusted host patterns
try {
  $settings_file = 'sites/default/settings.php';
  $settings_content = file_get_contents($settings_file);
  
  // Check if trusted host patterns are already in the file
  if (strpos($settings_content, 'trusted_host_patterns') !== false) {
    echo "<p>Trusted host patterns already exist in settings.php</p>";
  } else {
    echo "<p>Adding trusted host patterns to settings.php</p>";
    
    // Add trusted host patterns at the end of the file
    $patterns = '$settings[\'trusted_host_patterns\'] = [
  \'^drupal-tnb\.onrender\.com$\',
  \'^localhost$\',
  \'^127\.0\.0\.1$\',
];';
    
    file_put_contents($settings_file, $settings_content . "\n\n// Added by fix-site.php\n" . $patterns);
    
    echo "<p>✅ Added trusted host patterns to settings.php</p>";
  }
} catch (Exception $e) {
  echo "<p>❌ Error updating settings.php: " . htmlspecialchars($e->getMessage()) . "</p>";
}

// Test file write capability
$test_file = 'sites/default/files/test-write.txt';
try {
  file_put_contents($test_file, 'This is a test file to verify write permissions. Created at ' . date('Y-m-d H:i:s'));
  echo "<p>✅ Successfully wrote test file to verify permissions</p>";
} catch (Exception $e) {
  echo "<p>❌ Error writing test file: " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "<p>Repair script completed. You should now try accessing your Drupal site.</p>";
echo "<p><a href=\"/\">Click here to go to your site's homepage</a></p>"; 