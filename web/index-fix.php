<?php

/**
 * Fixed index.php for Drupal
 * Fixes variable definition issues by properly defining $app_root and $site_path 
 * before bootstrapping Drupal
 */

// Enable error reporting in development
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Define required variables
$app_root = dirname(__FILE__);
$site_path = 'sites/default';

// Define DRUPAL_ROOT if not already defined
if (!defined('DRUPAL_ROOT')) {
  define('DRUPAL_ROOT', $app_root);
}

// Try to include the autoloader
if (!file_exists($app_root . '/autoload.php')) {
  die('Could not find autoload.php. Make sure Composer dependencies are installed.');
}

// Include the autoloader
$autoloader = require_once $app_root . '/autoload.php';

// Check if critical directories exist, if not create them
$critical_dirs = [
  $app_root . '/' . $site_path . '/files',
  $app_root . '/' . $site_path . '/files/css',
  $app_root . '/' . $site_path . '/files/js',
  $app_root . '/' . $site_path . '/files/php',
  $app_root . '/' . $site_path . '/files/styles',
  $app_root . '/' . $site_path . '/files/translations'
];

foreach ($critical_dirs as $dir) {
  if (!is_dir($dir)) {
    mkdir($dir, 0777, true);
  }
}

try {
  // Load the Drupal kernel
  $kernel = new \Drupal\Core\DrupalKernel('prod', $autoloader);
  $kernel->setSitePath($site_path);
  $kernel->boot();
  $request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();
  $response = $kernel->handle($request);
  $response->send();
  $kernel->terminate($request, $response);
} catch (Exception $e) {
  // If there's an error during bootstrap, output it
  echo '<h1>Drupal Bootstrap Error</h1>';
  echo '<p>An error occurred during Drupal bootstrap:</p>';
  echo '<pre>' . htmlspecialchars($e->getMessage()) . '</pre>';
  echo '<pre>' . htmlspecialchars($e->getTraceAsString()) . '</pre>';
  
  // Output some debugging info
  echo '<h2>Environment Information</h2>';
  echo '<p>PHP Version: ' . phpversion() . '</p>';
  echo '<p>App Root: ' . $app_root . '</p>';
  echo '<p>Site Path: ' . $site_path . '</p>';
  
  // Check for settings.php
  $settings_file = $app_root . '/' . $site_path . '/settings.php';
  echo '<p>Settings.php: ' . (file_exists($settings_file) ? 'Exists' : 'Missing') . '</p>';
  
  // Check for autoload.php
  echo '<p>Autoloader: ' . (isset($autoloader) ? 'Loaded' : 'Failed to load') . '</p>';
} 