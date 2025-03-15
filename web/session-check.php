<?php

/**
 * Session and Cookie Check for Drupal
 * This script verifies session and cookie functionality
 */

// Enable error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Start session
session_start();

// Security check - remove in production
echo "<h1>Session and Cookie Check</h1>";

// Session status
echo "<h2>Session Status</h2>";
$session_status = session_status();
$session_status_text = [
    PHP_SESSION_DISABLED => 'Sessions are disabled',
    PHP_SESSION_NONE => 'Sessions are enabled but no session exists',
    PHP_SESSION_ACTIVE => 'Sessions are enabled and a session exists'
];

echo "<p>Session status: " . $session_status_text[$session_status] . " (" . $session_status . ")</p>";
echo "<p>Session ID: " . session_id() . "</p>";
echo "<p>Session name: " . session_name() . "</p>";

// Session configuration
echo "<h2>Session Configuration</h2>";
$session_config = [
    'session.save_handler' => ini_get('session.save_handler'),
    'session.save_path' => ini_get('session.save_path'),
    'session.use_cookies' => ini_get('session.use_cookies'),
    'session.use_only_cookies' => ini_get('session.use_only_cookies'),
    'session.cookie_lifetime' => ini_get('session.cookie_lifetime'),
    'session.cookie_path' => ini_get('session.cookie_path'),
    'session.cookie_domain' => ini_get('session.cookie_domain'),
    'session.cookie_secure' => ini_get('session.cookie_secure'),
    'session.cookie_httponly' => ini_get('session.cookie_httponly'),
    'session.cookie_samesite' => ini_get('session.cookie_samesite'),
    'session.gc_maxlifetime' => ini_get('session.gc_maxlifetime'),
    'session.gc_probability' => ini_get('session.gc_probability'),
    'session.gc_divisor' => ini_get('session.gc_divisor')
];

echo "<ul>";
foreach ($session_config as $key => $value) {
    echo "<li>" . $key . ": " . ($value === "" ? "(empty)" : $value) . "</li>";
}
echo "</ul>";

// Cookie test
echo "<h2>Cookie Test</h2>";

// Check if we have cookies from a previous visit
if (isset($_COOKIE['drupal_test_cookie'])) {
    echo "<p>✅ Test cookie found! Value: " . htmlspecialchars($_COOKIE['drupal_test_cookie']) . "</p>";
} else {
    echo "<p>❌ No test cookie found. Setting one now...</p>";
}

// Set a new test cookie
$cookie_value = "Cookie Test " . date('Y-m-d H:i:s');
setcookie('drupal_test_cookie', $cookie_value, time() + 3600, '/', '', false, false);
echo "<p>Test cookie set with value: " . $cookie_value . "</p>";
echo "<p>Refresh this page to see if the cookie persists.</p>";

// Check if we have a session value from a previous visit
if (isset($_SESSION['drupal_test_session'])) {
    echo "<p>✅ Session value found! Value: " . htmlspecialchars($_SESSION['drupal_test_session']) . "</p>";
} else {
    echo "<p>❌ No session value found. Setting one now...</p>";
}

// Set a new session value
$_SESSION['drupal_test_session'] = "Session Test " . date('Y-m-d H:i:s');
echo "<p>Session value set to: " . $_SESSION['drupal_test_session'] . "</p>";
echo "<p>Refresh this page to see if the session persists.</p>";

// Check all cookies
echo "<h2>All Cookies</h2>";
if (empty($_COOKIE)) {
    echo "<p>❌ No cookies found.</p>";
} else {
    echo "<ul>";
    foreach ($_COOKIE as $name => $value) {
        if (is_array($value)) {
            echo "<li>" . $name . ": (Array)</li>";
        } else {
            echo "<li>" . $name . ": " . htmlspecialchars($value) . "</li>";
        }
    }
    echo "</ul>";
}

// Check for Drupal cookie settings
echo "<h2>Environment Variables</h2>";
$important_env_vars = [
    'HTTP_HOST',
    'HTTPS',
    'REQUEST_SCHEME',
    'HTTP_USER_AGENT',
    'REMOTE_ADDR',
    'HTTP_X_FORWARDED_FOR',
    'HTTP_X_FORWARDED_PROTO'
];

echo "<ul>";
foreach ($important_env_vars as $var) {
    echo "<li>" . $var . ": " . (isset($_SERVER[$var]) ? htmlspecialchars($_SERVER[$var]) : 'Not set') . "</li>";
}
echo "</ul>";

// Write a simple test to session directory
echo "<h2>Session Directory Test</h2>";
$session_save_path = ini_get('session.save_path');
echo "<p>Session save path: " . ($session_save_path ?: '(default)') . "</p>";

if ($session_save_path) {
    if (is_dir($session_save_path)) {
        echo "<p>✅ Session directory exists.</p>";
        if (is_writable($session_save_path)) {
            echo "<p>✅ Session directory is writable.</p>";
            
            // Try to write a test file
            $test_file = $session_save_path . '/test_' . time() . '.txt';
            $write_success = false;
            $write_error = '';
            
            try {
                $handle = fopen($test_file, 'w');
                if ($handle) {
                    $write_success = fwrite($handle, 'Session directory test file created at ' . date('Y-m-d H:i:s'));
                    fclose($handle);
                }
            } catch (Exception $e) {
                $write_error = $e->getMessage();
            }
            
            if ($write_success) {
                echo "<p>✅ Successfully created test file in session directory.</p>";
                // Clean up
                if (unlink($test_file)) {
                    echo "<p>✅ Successfully deleted test file.</p>";
                } else {
                    echo "<p>❌ Failed to delete test file.</p>";
                }
            } else {
                echo "<p>❌ Failed to create test file in session directory. Error: " . $write_error . "</p>";
            }
        } else {
            echo "<p>❌ Session directory is not writable.</p>";
        }
    } else {
        echo "<p>❌ Session directory does not exist.</p>";
    }
} else {
    echo "<p>⚠️ Using default session save path.</p>";
}

// Include timestamp
echo "<hr>";
echo "<p>Check completed at: " . date('Y-m-d H:i:s') . "</p>"; 