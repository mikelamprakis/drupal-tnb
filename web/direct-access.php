<?php

/**
 * Direct access script for Drupal
 * Properly bootstraps Drupal and redirects to the homepage
 */

// Define the app_root and site_path variables
$app_root = dirname(__FILE__);
$site_path = 'sites/default';

// Define DRUPAL_ROOT if not already defined
if (!defined('DRUPAL_ROOT')) {
  define('DRUPAL_ROOT', $app_root);
}

// Try to bootstrap Drupal
try {
  // Include the autoloader
  require_once $app_root . '/autoload.php';
  
  // Tell Drupal where to find settings.php
  $_SERVER['SCRIPT_NAME'] = '/index.php';
  $_SERVER['SCRIPT_FILENAME'] = $app_root . '/index.php';
  $_SERVER['HTTP_HOST'] = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'drupal-tnb.onrender.com';
  
  // Load Drupal bootstrap
  $kernel = new \Drupal\Core\DrupalKernel('prod', $autoloader);
  $kernel->setSitePath($site_path);
  $kernel->boot();
  
  // Set up the request
  $request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();
  $response = $kernel->handle($request);
  $response->send();
  
  $kernel->terminate($request, $response);
} catch (Exception $e) {
  // If bootstrapping fails, provide helpful information and redirect
  echo "<h1>Direct Access Redirect</h1>";
  echo "<p>Error bootstrapping Drupal: " . htmlspecialchars($e->getMessage()) . "</p>";
  echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
  echo "<p>Redirecting to homepage...</p>";
  
  // Meta refresh to homepage
  echo '<meta http-equiv="refresh" content="5;url=/" />';
} 