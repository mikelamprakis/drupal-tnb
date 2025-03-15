<?php

/**
 * Error debugging script for Drupal
 * Attempts to load Drupal bootstrap while displaying all errors
 */

// Force display of all errors
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>Drupal Error Debug</h1>";
echo "<p>Testing Drupal bootstrap with error reporting enabled</p>";

// Try to get the Drupal root path
$root_path = dirname(__FILE__);
echo "<p>Root path: " . $root_path . "</p>";

// Try to bootstrap Drupal with error reporting
try {
  // Define the app_root and site_path variables
  $app_root = $root_path;
  $site_path = 'sites/default';
  
  echo "<p>Attempting to load settings.php...</p>";
  if (file_exists($app_root . '/' . $site_path . '/settings.php')) {
    echo "<p>✅ settings.php exists</p>";
    
    // Include settings.php with properly defined variables
    global $app_root, $site_path;
    $app_root = $root_path;
    $site_path = 'sites/default';
    
    require_once $app_root . '/' . $site_path . '/settings.php';
    echo "<p>✅ settings.php loaded</p>";
    
    // Check for autoload.php
    echo "<p>Checking for autoload.php...</p>";
    if (file_exists($app_root . '/autoload.php')) {
      echo "<p>✅ autoload.php exists</p>";
      
      // Include the autoloader and capture the return value
      $autoloader = require_once $app_root . '/autoload.php';
      echo "<p>✅ autoload.php loaded, autoloader captured</p>";
      
      // Try to use Drupal's kernel class
      echo "<p>Attempting to bootstrap Drupal...</p>";
      
      // Try to find the request class
      if (class_exists('Symfony\Component\HttpFoundation\Request')) {
        echo "<p>✅ Request class found</p>";
        
        // Create a request
        $request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();
        echo "<p>✅ Request created</p>";
        
        // Try to find the kernel class
        if (class_exists('Drupal\Core\DrupalKernel')) {
          echo "<p>✅ DrupalKernel class found</p>";
          
          // Try to boot the kernel
          try {
            $kernel = \Drupal\Core\DrupalKernel::createFromRequest($request, $autoloader, 'prod');
            echo "<p>✅ Kernel created</p>";
            
            // Try to boot the kernel
            $kernel->boot();
            echo "<p>✅ Kernel booted</p>";
            
            // Try to get the service container
            $container = $kernel->getContainer();
            echo "<p>✅ Service container available</p>";
            
            // Check if we can get a service
            if ($container->has('current_user')) {
              echo "<p>✅ Current user service available</p>";
              $current_user = $container->get('current_user');
              echo "<p>Current user ID: " . $current_user->id() . "</p>";
            } else {
              echo "<p>❌ Current user service not available</p>";
            }
          } catch (Exception $e) {
            echo "<p>❌ Error while booting kernel: " . htmlspecialchars($e->getMessage()) . "</p>";
            echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
          }
        } else {
          echo "<p>❌ DrupalKernel class not found</p>";
        }
      } else {
        echo "<p>❌ Request class not found</p>";
      }
    } else {
      echo "<p>❌ autoload.php not found</p>";
    }
  } else {
    echo "<p>❌ settings.php not found</p>";
  }
} catch (Exception $e) {
  echo "<p>❌ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
  echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
} 