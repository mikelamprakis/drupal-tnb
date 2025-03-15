<?php

/**
 * This file is included by settings.php to ensure trusted hosts are properly configured.
 */

$settings['trusted_host_patterns'] = [
  '^drupal\-tnb\.onrender\.com$',
  '^drupal\-tnb\.ddev\.site$',
  '^localhost$',
  '^127\.0\.0\.1$',
];

// Ensure we have the sites/default/files/css and js directories
$dirs = [
  'sites/default/files/css',
  'sites/default/files/js',
  'sites/default/files/php',
  'sites/default/files/styles',
];

foreach ($dirs as $dir) {
  $full_path = DRUPAL_ROOT . '/' . $dir;
  if (!file_exists($full_path)) {
    @mkdir($full_path, 0777, TRUE);
  }
} 