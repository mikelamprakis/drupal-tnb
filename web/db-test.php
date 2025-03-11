<?php

echo '<h1>Database Connection Test</h1>';

// Display DATABASE_URL (with password partially redacted for security)
$db_url = getenv('DATABASE_URL');
if ($db_url) {
  // Redact password
  $masked_url = preg_replace('/(:)([^@]*)(@)/', '$1*****$3', $db_url);
  echo "<p>DATABASE_URL is set: $masked_url</p>";
  
  try {
    // Parse the DATABASE_URL
    $parsed = parse_url($db_url);
    echo "<pre>";
    echo "Host: " . $parsed['host'] . "\n";
    echo "Port: " . ($parsed['port'] ?? '5432') . "\n";
    echo "Database: " . ltrim($parsed['path'], '/') . "\n";
    echo "Username: " . $parsed['user'] . "\n";
    echo "</pre>";
    
    // Try to connect
    $dsn = sprintf(
      "pgsql:host=%s;port=%s;dbname=%s;user=%s;password=%s", 
      $parsed['host'], 
      $parsed['port'] ?? '5432', 
      ltrim($parsed['path'], '/'), 
      $parsed['user'], 
      $parsed['pass']
    );
    
    $pdo = new PDO($dsn);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<p style='color: green; font-weight: bold;'>✅ Database connection successful!</p>";
    
    // Test a query
    $stmt = $pdo->query("SELECT version()");
    $version = $stmt->fetchColumn();
    echo "<p>PostgreSQL version: $version</p>";
    
  } catch (Exception $e) {
    echo "<p style='color: red; font-weight: bold;'>❌ Database connection failed: " . $e->getMessage() . "</p>";
  }
} else {
  echo "<p style='color: red; font-weight: bold;'>❌ DATABASE_URL environment variable is not set!</p>";
}

// Check if settings.php exists and has database settings
if (file_exists(__DIR__ . '/sites/default/settings.php')) {
  echo "<p>settings.php exists</p>";
} else {
  echo "<p style='color: red;'>settings.php does not exist!</p>";
} 