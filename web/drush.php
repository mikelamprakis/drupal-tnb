<?php
/**
 * This is a special web-executable Drush script for Render.com.
 * It allows running specific Drush commands through a web request.
 * 
 * IMPORTANT: Delete this file after site installation!
 */

// Basic security - only allow from localhost or with a secret
$secret = getenv('SETUP_SECRET') ?: md5(__FILE__);
$allowed = false;

if (isset($_GET['secret']) && $_GET['secret'] === $secret) {
  $allowed = true;
}
elseif ($_SERVER['REMOTE_ADDR'] === '127.0.0.1' || $_SERVER['REMOTE_ADDR'] === '::1') {
  $allowed = true;
}

if (!$allowed) {
  header('HTTP/1.0 403 Forbidden');
  echo "Access denied.";
  exit;
}

// Make sure we're in the web root
chdir(dirname(__FILE__));

// Set up
$command = isset($_GET['command']) ? $_GET['command'] : '';
$allowed_commands = [
  'status',
  'site:install',
  'cache:rebuild',
  'updatedb',
  'config:import',
];

// Validate the command
$safe_command = '';
foreach ($allowed_commands as $allowed) {
  if (strpos($command, $allowed) === 0) {
    $safe_command = escapeshellcmd($command);
    break;
  }
}

if (empty($safe_command)) {
  header('HTTP/1.0 400 Bad Request');
  echo "Invalid command.";
  exit;
}

// Set up the Drush environment
putenv('DRUSH_OPTIONS_URI=' . (isset($_SERVER['HTTP_HOST']) ? 'https://' . $_SERVER['HTTP_HOST'] : 'http://localhost'));

// Run the command
$output = [];
$return_var = 0;
exec('../vendor/bin/drush ' . $safe_command . ' 2>&1', $output, $return_var);

// Output
header('Content-Type: text/plain');
echo "Running: drush " . $safe_command . "\n\n";
echo implode("\n", $output) . "\n";
echo "\nExited with code: " . $return_var . "\n"; 