<?php

/**
 * Environment Check for Drupal on Render
 * This script checks various server configuration aspects
 */

// Enable error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Security check - remove in production
echo "<h1>Environment Check</h1>";

// Report PHP version and extensions
echo "<h2>PHP Environment</h2>";
echo "<p>PHP Version: " . phpversion() . "</p>";

echo "<h3>Required PHP Extensions</h3>";
$required_extensions = ['gd', 'pdo', 'xml', 'json', 'mbstring'];
echo "<ul>";
foreach ($required_extensions as $ext) {
    $loaded = extension_loaded($ext);
    echo "<li>" . $ext . ": " . ($loaded ? "✅ Loaded" : "❌ Not loaded") . "</li>";
}
echo "</ul>";

// File system checks
echo "<h2>File System</h2>";
$app_root = dirname(__FILE__);
echo "<p>App Root: " . $app_root . "</p>";

$directory_checks = [
    'sites/default/files',
    'sites/default/files/css',
    'sites/default/files/js',
    'sites/default/files/php',
    'sites/default/files/styles',
    'sites/default/files/translations'
];

echo "<h3>Directory Checks</h3>";
echo "<ul>";
foreach ($directory_checks as $dir) {
    $full_path = $app_root . '/' . $dir;
    $exists = is_dir($full_path);
    $writable = $exists && is_writable($full_path);
    $perms = $exists ? substr(sprintf('%o', fileperms($full_path)), -4) : 'N/A';
    
    echo "<li>" . $dir . ": " . 
         ($exists ? "✅ Exists" : "❌ Missing") . 
         " | Permissions: " . $perms . 
         " | " . ($writable ? "✅ Writable" : "❌ Not writable") . "</li>";
}
echo "</ul>";

// File check
echo "<h3>Critical File Checks</h3>";
$file_checks = [
    'autoload.php',
    'index.php',
    'sites/default/settings.php',
    'sites/default/default.settings.php',
    'sites/default/trusted-hosts-fix.php'
];

echo "<ul>";
foreach ($file_checks as $file) {
    $full_path = $app_root . '/' . $file;
    $exists = file_exists($full_path);
    $readable = $exists && is_readable($full_path);
    $size = $exists ? filesize($full_path) : 0;
    $perms = $exists ? substr(sprintf('%o', fileperms($full_path)), -4) : 'N/A';
    
    echo "<li>" . $file . ": " . 
         ($exists ? "✅ Exists" : "❌ Missing") . 
         " | Size: " . $size . " bytes" .
         " | Permissions: " . $perms . 
         " | " . ($readable ? "✅ Readable" : "❌ Not readable") . "</li>";
}
echo "</ul>";

// Database connectivity check
echo "<h2>Database Connectivity</h2>";
try {
    // Check if settings.php exists and attempt to extract DB credentials
    $settings_file = $app_root . '/sites/default/settings.php';
    if (file_exists($settings_file)) {
        // We'll try to parse the file to find DB credentials
        $settings_content = file_get_contents($settings_file);
        
        // Look for database settings
        $db_info = [];
        if (preg_match('/databases\s*\[\s*\'default\'\s*\]\s*\[\s*\'default\'\s*\]\s*=\s*array\s*\((.*?)\)\s*;/s', $settings_content, $matches)) {
            $db_section = $matches[1];
            
            // Extract database type
            if (preg_match('/\'driver\'\s*=>\s*\'(.*?)\'/s', $db_section, $driver_match)) {
                $db_info['driver'] = $driver_match[1];
            }
            
            // Extract database name
            if (preg_match('/\'database\'\s*=>\s*\'(.*?)\'/s', $db_section, $db_match)) {
                $db_info['database'] = $db_match[1];
            }
            
            // Extract host
            if (preg_match('/\'host\'\s*=>\s*\'(.*?)\'/s', $db_section, $host_match)) {
                $db_info['host'] = $host_match[1];
            }
            
            echo "<p>Found database configuration:</p>";
            echo "<ul>";
            foreach ($db_info as $key => $value) {
                echo "<li>" . $key . ": " . $value . "</li>";
            }
            echo "</ul>";
            
            // Try to connect to the database (without revealing credentials)
            if (!empty($db_info['driver']) && !empty($db_info['database']) && !empty($db_info['host'])) {
                echo "<p>Attempting database connection (credentials masked for security)...</p>";
                
                try {
                    // Create a PDO connection
                    $pdo = new PDO(
                        $db_info['driver'] . ':host=' . $db_info['host'] . ';dbname=' . $db_info['database'],
                        '***', // Username masked for security
                        '***'  // Password masked for security
                    );
                    echo "<p>✅ Successfully connected to the database!</p>";
                    
                    // Check if essential tables exist
                    $essential_tables = ['config', 'users', 'node', 'cache_bootstrap', 'cache_discovery'];
                    echo "<p>Checking essential Drupal tables:</p>";
                    echo "<ul>";
                    foreach ($essential_tables as $table) {
                        try {
                            $stmt = $pdo->query("SELECT 1 FROM " . $table . " LIMIT 1");
                            $exists = ($stmt !== false);
                            echo "<li>" . $table . ": " . ($exists ? "✅ Exists" : "❌ Missing") . "</li>";
                        } catch (PDOException $e) {
                            echo "<li>" . $table . ": ❌ Error checking - " . $e->getMessage() . "</li>";
                        }
                    }
                    echo "</ul>";
                    
                } catch (PDOException $e) {
                    echo "<p>❌ Database connection failed: " . $e->getMessage() . "</p>";
                }
            } else {
                echo "<p>❌ Incomplete database configuration found.</p>";
            }
        } else {
            echo "<p>❌ Could not find database configuration in settings.php.</p>";
        }
    } else {
        echo "<p>❌ settings.php not found.</p>";
    }
} catch (Exception $e) {
    echo "<p>❌ Error checking database: " . $e->getMessage() . "</p>";
}

// Server environment
echo "<h2>Server Environment</h2>";
echo "<p>Server Software: " . ($_SERVER['SERVER_SOFTWARE'] ?? 'Unknown') . "</p>";

// Check for important environment variables
$important_env_vars = [
    'DRUPAL_APP_ROOT',
    'DRUPAL_SITE_PATH',
    'HTTP_HOST',
    'REMOTE_ADDR',
    'SERVER_NAME',
    'REQUEST_URI',
    'SCRIPT_NAME',
    'DOCUMENT_ROOT'
];

echo "<h3>Key Environment Variables</h3>";
echo "<ul>";
foreach ($important_env_vars as $var) {
    echo "<li>" . $var . ": " . (isset($_SERVER[$var]) ? htmlspecialchars($_SERVER[$var]) : 'Not set') . "</li>";
}
echo "</ul>";

// Test file writing
echo "<h2>File Writing Test</h2>";
$test_file = $app_root . '/sites/default/files/test_' . time() . '.txt';
$write_success = false;
$write_error = '';

try {
    $handle = fopen($test_file, 'w');
    if ($handle) {
        $write_success = fwrite($handle, 'This is a test file created at ' . date('Y-m-d H:i:s'));
        fclose($handle);
    }
} catch (Exception $e) {
    $write_error = $e->getMessage();
}

if ($write_success) {
    echo "<p>✅ Successfully created test file: " . $test_file . "</p>";
    // Clean up
    if (unlink($test_file)) {
        echo "<p>✅ Successfully deleted test file.</p>";
    } else {
        echo "<p>❌ Failed to delete test file.</p>";
    }
} else {
    echo "<p>❌ Failed to create test file. Error: " . $write_error . "</p>";
}

// Include timestamp
echo "<hr>";
echo "<p>Check completed at: " . date('Y-m-d H:i:s') . "</p>"; 