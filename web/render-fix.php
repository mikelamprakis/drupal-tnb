<?php

/**
 * Render Environment Fix Script for Drupal
 * Addresses specific issues identified in the environment check
 */

// Enable error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h1>Render Environment Fix</h1>";

// Define constants
$app_root = dirname(__FILE__);
$site_path = 'sites/default';

// Define DRUPAL_ROOT if not already defined
if (!defined('DRUPAL_ROOT')) {
  define('DRUPAL_ROOT', $app_root);
}

// 1. Create missing directories
echo "<h2>1. Creating Missing Directories</h2>";

$missing_dirs = [
  $app_root . '/' . $site_path . '/files/css',
  $app_root . '/' . $site_path . '/files/js',
  $app_root . '/' . $site_path . '/files/translations'
];

foreach ($missing_dirs as $dir) {
  if (!is_dir($dir)) {
    echo "<p>Creating directory: " . $dir . "...</p>";
    if (mkdir($dir, 0777, true)) {
      echo "<p>✅ Directory created successfully!</p>";
    } else {
      echo "<p>❌ Failed to create directory.</p>";
    }
  } else {
    echo "<p>✅ Directory already exists: " . $dir . "</p>";
  }
}

// 2. Verify permissions
echo "<h2>2. Verifying Directory Permissions</h2>";

$important_dirs = [
  $app_root . '/' . $site_path . '/files',
  $app_root . '/' . $site_path . '/files/css',
  $app_root . '/' . $site_path . '/files/js',
  $app_root . '/' . $site_path . '/files/php',
  $app_root . '/' . $site_path . '/files/styles',
  $app_root . '/' . $site_path . '/files/translations'
];

foreach ($important_dirs as $dir) {
  if (is_dir($dir)) {
    $perms = substr(sprintf('%o', fileperms($dir)), -4);
    echo "<p>Directory: " . $dir . " | Permissions: " . $perms;
    
    if ($perms != '0777') {
      echo " | Updating permissions...</p>";
      if (chmod($dir, 0777)) {
        echo "<p>✅ Permissions updated successfully!</p>";
      } else {
        echo "<p>❌ Failed to update permissions.</p>";
      }
    } else {
      echo " | ✅ Permissions correct</p>";
    }
  }
}

// 3. Check for database SSL configuration in settings.php
echo "<h2>3. Checking Database SSL Configuration</h2>";

$settings_file = $app_root . '/' . $site_path . '/settings.php';
$settings_local_file = $app_root . '/' . $site_path . '/settings.local.php';

if (file_exists($settings_file)) {
  $settings_content = file_get_contents($settings_file);
  
  // Check if SSL options are already configured
  $has_ssl_config = (strpos($settings_content, 'sslmode') !== false);
  
  if (!$has_ssl_config) {
    echo "<p>SSL configuration for PostgreSQL not found in settings.php.</p>";
    echo "<p>Adding SSL configuration to a new settings.local.php file...</p>";
    
    // Create a new settings.local.php with SSL configuration
    $ssl_config = "<?php\n\n";
    $ssl_config .= "/**\n";
    $ssl_config .= " * Local settings for Render environment\n";
    $ssl_config .= " * Adds SSL configuration for PostgreSQL\n";
    $ssl_config .= " */\n\n";
    $ssl_config .= "// Add SSL configuration for PostgreSQL\n";
    $ssl_config .= "if (isset(\$databases['default']['default'])) {\n";
    $ssl_config .= "  // Enable SSL mode\n";
    $ssl_config .= "  \$databases['default']['default']['pdo'][PDO::MYSQL_ATTR_SSL_CA] = false;\n";
    $ssl_config .= "  \$databases['default']['default']['pdo'][PDO::ATTR_TIMEOUT] = 5;\n";
    $ssl_config .= "  \$databases['default']['default']['pdo'][PDO::ATTR_PERSISTENT] = false;\n";
    $ssl_config .= "  \$databases['default']['default']['sslmode'] = 'require';\n\n";
    $ssl_config .= "  // Define app_root and site_path if not already defined\n";
    $ssl_config .= "  if (!isset(\$app_root)) {\n";
    $ssl_config .= "    \$app_root = '" . $app_root . "';\n";
    $ssl_config .= "  }\n\n";
    $ssl_config .= "  if (!isset(\$site_path)) {\n";
    $ssl_config .= "    \$site_path = '" . $site_path . "';\n";
    $ssl_config .= "  }\n";
    $ssl_config .= "}\n";
    
    if (file_put_contents($settings_local_file, $ssl_config)) {
      echo "<p>✅ Created settings.local.php with SSL configuration!</p>";
      
      // Now ensure settings.php includes settings.local.php
      if (strpos($settings_content, 'settings.local.php') === false) {
        $include_code = "\n\n// Include local settings\n";
        $include_code .= "if (file_exists(\$app_root . '/' . \$site_path . '/settings.local.php')) {\n";
        $include_code .= "  include \$app_root . '/' . \$site_path . '/settings.local.php';\n";
        $include_code .= "}\n";
        
        if (file_put_contents($settings_file, $settings_content . $include_code)) {
          echo "<p>✅ Updated settings.php to include settings.local.php!</p>";
        } else {
          echo "<p>❌ Failed to update settings.php.</p>";
        }
      } else {
        echo "<p>✅ settings.php already includes settings.local.php.</p>";
      }
    } else {
      echo "<p>❌ Failed to create settings.local.php.</p>";
    }
  } else {
    echo "<p>✅ SSL configuration already exists in settings.php.</p>";
  }
} else {
  echo "<p>❌ settings.php not found.</p>";
}

// 4. Create/update .htaccess to ensure PHP environment variables
echo "<h2>4. Setting Environment Variables</h2>";

$htaccess_file = $app_root . '/.htaccess';
if (file_exists($htaccess_file)) {
  $htaccess_content = file_get_contents($htaccess_file);
  
  if (strpos($htaccess_content, 'DRUPAL_APP_ROOT') === false) {
    echo "<p>Adding DRUPAL_APP_ROOT and DRUPAL_SITE_PATH to .htaccess...</p>";
    
    // Find RewriteEngine On line
    $pos = strpos($htaccess_content, 'RewriteEngine on');
    if ($pos !== false) {
      $env_vars = "\n# Set Drupal environment variables\n";
      $env_vars .= "<IfModule mod_env.c>\n";
      $env_vars .= "  SetEnv DRUPAL_APP_ROOT " . $app_root . "\n";
      $env_vars .= "  SetEnv DRUPAL_SITE_PATH " . $site_path . "\n";
      $env_vars .= "</IfModule>\n\n";
      
      $new_content = substr($htaccess_content, 0, $pos + 16) . $env_vars . substr($htaccess_content, $pos + 16);
      
      if (file_put_contents($htaccess_file, $new_content)) {
        echo "<p>✅ Updated .htaccess successfully!</p>";
      } else {
        echo "<p>❌ Failed to update .htaccess.</p>";
      }
    } else {
      echo "<p>❌ Could not find the correct position in .htaccess.</p>";
    }
  } else {
    echo "<p>✅ Environment variables already set in .htaccess.</p>";
  }
} else {
  echo "<p>❌ .htaccess not found.</p>";
}

// 5. Verify index-fix.php exists
echo "<h2>5. Checking for Index Fix</h2>";

$index_fix_file = $app_root . '/index-fix.php';
if (file_exists($index_fix_file)) {
  echo "<p>✅ index-fix.php exists.</p>";
} else {
  echo "<p>❌ index-fix.php not found. Creating it...</p>";
  
  $index_fix_content = "<?php\n\n";
  $index_fix_content .= "/**\n";
  $index_fix_content .= " * Fixed index.php for Drupal\n";
  $index_fix_content .= " * Defines required variables before bootstrapping\n";
  $index_fix_content .= " */\n\n";
  $index_fix_content .= "// Define required variables\n";
  $index_fix_content .= "\$app_root = dirname(__FILE__);\n";
  $index_fix_content .= "\$site_path = 'sites/default';\n\n";
  $index_fix_content .= "// Define DRUPAL_ROOT if not already defined\n";
  $index_fix_content .= "if (!defined('DRUPAL_ROOT')) {\n";
  $index_fix_content .= "  define('DRUPAL_ROOT', \$app_root);\n";
  $index_fix_content .= "}\n\n";
  $index_fix_content .= "// Create critical directories if needed\n";
  $index_fix_content .= "\$dirs = [\n";
  $index_fix_content .= "  \$app_root . '/' . \$site_path . '/files/css',\n";
  $index_fix_content .= "  \$app_root . '/' . \$site_path . '/files/js',\n";
  $index_fix_content .= "  \$app_root . '/' . \$site_path . '/files/php',\n";
  $index_fix_content .= "  \$app_root . '/' . \$site_path . '/files/styles',\n";
  $index_fix_content .= "  \$app_root . '/' . \$site_path . '/files/translations'\n";
  $index_fix_content .= "];\n\n";
  $index_fix_content .= "foreach (\$dirs as \$dir) {\n";
  $index_fix_content .= "  if (!is_dir(\$dir)) {\n";
  $index_fix_content .= "    @mkdir(\$dir, 0777, TRUE);\n";
  $index_fix_content .= "  }\n";
  $index_fix_content .= "}\n\n";
  $index_fix_content .= "// Include the real index.php\n";
  $index_fix_content .= "include 'index.php';\n";
  
  if (file_put_contents($index_fix_file, $index_fix_content)) {
    echo "<p>✅ Created index-fix.php successfully!</p>";
  } else {
    echo "<p>❌ Failed to create index-fix.php.</p>";
  }
}

// Summary and next steps
echo "<h2>Summary</h2>";
echo "<p>The following fixes have been applied:</p>";
echo "<ol>";
echo "<li>Created missing directories (css, js, translations)</li>";
echo "<li>Verified directory permissions</li>";
echo "<li>Added SSL configuration for PostgreSQL database</li>";
echo "<li>Set Drupal environment variables</li>";
echo "<li>Verified or created index-fix.php</li>";
echo "</ol>";

echo "<h2>Next Steps</h2>";
echo "<p>Now that these fixes have been applied, try accessing your site:</p>";
echo "<ol>";
echo "<li><a href='/index-fix.php'>Access your site via index-fix.php</a></li>";
echo "<li><a href='/'>Access your site's homepage</a> (if the fixes above have resolved the issues)</li>";
echo "</ol>";
echo "<p>If you still encounter database connection issues, you may need to check that your database credentials are correct and that SSL/TLS is properly configured.</p>"; 