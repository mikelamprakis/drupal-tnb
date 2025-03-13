<?php

/**
 * Database connection test script
 * 
 * This script tests the connection to the remote Render.com PostgreSQL database.
 * It should be accessed via the web browser or with 'ddev exec php web/db-connection-test.php'
 */

// For web access, show errors
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>Remote Database Connection Test</h1>";

// Check if the environment variable is set
$render_db_url = getenv('RENDER_DATABASE_URL');

if (empty($render_db_url)) {
  echo "<p style='color: red;'>Error: RENDER_DATABASE_URL environment variable is not set.</p>";
  echo "<p>Please set this environment variable in .ddev/config.yaml with your actual Render.com database URL.</p>";
  echo "<pre>web_environment:\n  - RENDER_DATABASE_URL=postgres://username:password@host:port/database</pre>";
  exit(1);
}

echo "<p>Found RENDER_DATABASE_URL environment variable.</p>";

// Parse the database URL
$db_url = parse_url($render_db_url);

echo "<p>Attempting to connect to remote PostgreSQL database:</p>";
echo "<ul>";
echo "<li>Host: " . $db_url['host'] . "</li>";
echo "<li>Port: " . ($db_url['port'] ?? '5432') . "</li>";
echo "<li>Database: " . ltrim($db_url['path'], '/') . "</li>";
echo "<li>Username: " . $db_url['user'] . "</li>";
echo "</ul>";

// Check if PostgreSQL extension is loaded
if (!extension_loaded('pgsql')) {
  echo "<p style='color: red;'>Error: PostgreSQL extension is not loaded.</p>";
  echo "<p>Please make sure the PHP pgsql extension is installed in your DDEV environment.</p>";
  exit(1);
}

echo "<p>PostgreSQL extension is loaded.</p>";

// Try to connect to the database
try {
  $dsn = sprintf(
    'pgsql:host=%s;port=%s;dbname=%s;user=%s;password=%s',
    $db_url['host'],
    $db_url['port'] ?? '5432',
    ltrim($db_url['path'], '/'),
    $db_url['user'],
    $db_url['pass']
  );
  
  $pdo = new PDO($dsn);
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  
  echo "<p style='color: green;'>Successfully connected to the remote PostgreSQL database!</p>";
  
  // Check if we can access Drupal tables
  $stmt = $pdo->query("SELECT table_name FROM information_schema.tables WHERE table_schema = 'public' LIMIT 10");
  $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
  
  echo "<p>Found " . count($tables) . " tables in the database:</p>";
  echo "<ul>";
  foreach ($tables as $table) {
    echo "<li>" . htmlspecialchars($table) . "</li>";
  }
  echo "</ul>";
  
  echo "<p>Connection test completed successfully. You can now use your local Drupal with the remote database.</p>";
  
} catch (PDOException $e) {
  echo "<p style='color: red;'>Error connecting to the database: " . htmlspecialchars($e->getMessage()) . "</p>";
  echo "<p>Please check your database credentials and make sure the database is accessible from your local network.</p>";
  echo "<p>Note: Render.com databases typically require IP allowlisting or a secure tunnel.</p>";
} 