<?php

/**
 * Apply settings fix to Drupal
 * This script modifies settings.php to include the trusted-hosts-fix.php file
 */

// Display all errors
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>Apply Settings Fix</h1>";

$settings_file = 'sites/default/settings.php';
$fix_file = 'sites/default/trusted-hosts-fix.php';

// Check if files exist
if (!file_exists($settings_file)) {
  die("settings.php not found!");
}

if (!file_exists($fix_file)) {
  die("trusted-hosts-fix.php not found!");
}

// Get the content of settings.php
$settings_content = file_get_contents($settings_file);

// Check if fix is already included
if (strpos($settings_content, 'trusted-hosts-fix.php') !== false) {
  echo "<p>Fix is already included in settings.php</p>";
} else {
  // Prepare the include line
  $include_line = "\n\n# Include trusted hosts fix\nif (file_exists(__DIR__ . '/trusted-hosts-fix.php')) {\n  include __DIR__ . '/trusted-hosts-fix.php';\n}\n";
  
  // Append to settings.php
  $result = file_put_contents($settings_file, $settings_content . $include_line);
  
  if ($result !== false) {
    echo "<p>✅ Successfully added fix to settings.php</p>";
  } else {
    echo "<p>❌ Failed to write to settings.php</p>";
  }
}

echo "<p>Fix applied. You should now try accessing your Drupal site.</p>";
echo "<p><a href=\"/\">Click here to go to your site's homepage</a></p>"; 