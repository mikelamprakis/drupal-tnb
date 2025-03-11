<?php
// Basic test file to check if PHP is working
echo '<h1>PHP Test Page</h1>';
echo '<p>PHP version: ' . phpversion() . '</p>';

// Check database connection settings
echo '<h2>Environment Variables</h2>';
echo '<pre>';
print_r($_ENV);
echo '</pre>';

// Display loaded extensions
echo '<h2>Loaded Extensions</h2>';
echo '<pre>';
print_r(get_loaded_extensions());
echo '</pre>';

// Check if we can load core Drupal files
echo '<h2>Drupal Files Check</h2>';
$files_to_check = [
    '/var/www/html/web/index.php',
    '/var/www/html/web/core/lib/Drupal.php',
    '/var/www/html/web/sites/default/settings.php',
    '/var/www/html/web/sites/default/default.settings.php',
];

foreach ($files_to_check as $file) {
    echo "$file: " . (file_exists($file) ? 'EXISTS' : 'MISSING') . '<br>';
}

// Check directory permissions
echo '<h2>Directory Permissions</h2>';
$dirs_to_check = [
    '/var/www/html/web',
    '/var/www/html/web/sites',
    '/var/www/html/web/sites/default',
    '/var/www/html/web/sites/default/files',
];

foreach ($dirs_to_check as $dir) {
    echo "$dir: " . (is_writable($dir) ? 'WRITABLE' : 'NOT WRITABLE') . '<br>';
} 