<?php

/**
 * Database connection debug script.
 */

// Display all errors
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Get the database settings
require_once 'sites/default/settings.php';

echo "<h1>Database Connection Test</h1>";

try {
  // Extract database connection info
  $db_config = $databases['default']['default'];
  
  echo "<p>Attempting to connect to database:</p>";
  echo "<ul>";
  echo "<li>Driver: {$db_config['driver']}</li>";
  echo "<li>Host: {$db_config['host']}</li>";
  echo "<li>Database: {$db_config['database']}</li>";
  echo "<li>Username: {$db_config['username']}</li>";
  echo "<li>Port: {$db_config['port']}</li>";
  echo "</ul>";
  
  // Connect to database
  if ($db_config['driver'] == 'pgsql') {
    $dsn = "pgsql:host={$db_config['host']};port={$db_config['port']};dbname={$db_config['database']}";
    $options = [
      PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
      PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
      PDO::ATTR_EMULATE_PREPARES => false,
    ];
    
    // Add SSL if needed
    if (isset($db_config['sslmode']) && $db_config['sslmode'] === 'require') {
      $dsn .= ";sslmode=require";
      echo "<p>Using SSL connection</p>";
    }
    
    $pdo = new PDO($dsn, $db_config['username'], $db_config['password'], $options);
    
    // Test query
    $stmt = $pdo->query("SELECT table_name FROM information_schema.tables WHERE table_schema='public' LIMIT 10");
    $tables = $stmt->fetchAll();
    
    echo "<p>Successfully connected to the database!</p>";
    echo "<p>Found " . count($tables) . " tables:</p>";
    echo "<ul>";
    foreach ($tables as $table) {
      echo "<li>" . htmlspecialchars($table['table_name']) . "</li>";
    }
    echo "</ul>";
  } else {
    echo "<p>Not a PostgreSQL database. Driver: {$db_config['driver']}</p>";
  }
} catch (PDOException $e) {
  echo "<h2>Database Error:</h2>";
  echo "<pre>" . htmlspecialchars($e->getMessage()) . "</pre>";
  
  if (strpos($e->getMessage(), 'SSL connection') !== false) {
    echo "<p>This appears to be an SSL connection issue. Make sure the SSL requirements are properly configured.</p>";
  }
} 