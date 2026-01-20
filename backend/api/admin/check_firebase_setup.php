<?php
/**
 * Firebase Admin SDK Setup Checker
 * 
 * Run this file to verify your Firebase Admin SDK setup is correct.
 * Access via: http://localhost/metro/backend/api/admin/check_firebase_setup.php
 */

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Firebase Admin SDK Setup Check</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .success { color: green; }
        .error { color: red; }
        .warning { color: orange; }
        pre { background: #f5f5f5; padding: 10px; border-radius: 4px; }
    </style>
</head>
<body>
    <h1>Firebase Admin SDK Setup Check</h1>
    
    <?php
    $checks = [];
    
    // Check 1: Composer autoload
    // Try multiple path resolutions
    $possiblePaths = [
        __DIR__ . '/../../vendor/autoload.php',
        dirname(__DIR__) . '/vendor/autoload.php',
        dirname(dirname(__DIR__)) . '/vendor/autoload.php',
    ];
    
    $autoloadPath = null;
    foreach ($possiblePaths as $path) {
        $resolved = realpath($path);
        if ($resolved && file_exists($resolved)) {
            $autoloadPath = $resolved;
            break;
        }
    }
    
    if ($autoloadPath) {
        $checks[] = ['name' => 'Composer autoload exists', 'status' => 'success',
                     'message' => 'Found at: ' . $autoloadPath];
        require_once $autoloadPath;
    } else {
        $checks[] = ['name' => 'Composer autoload exists', 'status' => 'error', 
                     'message' => 'Expected at: backend/api/vendor/autoload.php - Run: cd backend/api && composer dump-autoload'];
    }
    
    // Check 2: Service account file
    $serviceAccountPath = __DIR__ . '/firebase-service-account.json';
    if (file_exists($serviceAccountPath)) {
        $checks[] = ['name' => 'Service account key file exists', 'status' => 'success'];
        
        // Check if it's valid JSON
        $jsonContent = file_get_contents($serviceAccountPath);
        $jsonData = json_decode($jsonContent, true);
        if ($jsonData && isset($jsonData['project_id'])) {
            $checks[] = ['name' => 'Service account key is valid JSON', 'status' => 'success',
                        'message' => 'Project ID: ' . $jsonData['project_id']];
        } else {
            $checks[] = ['name' => 'Service account key is valid JSON', 'status' => 'error',
                         'message' => 'Invalid JSON or missing project_id'];
        }
    } else {
        $checks[] = ['name' => 'Service account key file exists', 'status' => 'error',
                     'message' => 'Download from Firebase Console and save as: firebase-service-account.json'];
    }
    
    // Check 3: Firebase Admin SDK class
    if (file_exists($autoloadPath)) {
        try {
            if (class_exists('Kreait\Firebase\Factory')) {
                $checks[] = ['name' => 'Firebase Admin SDK classes loaded', 'status' => 'success'];
            } else {
                $checks[] = ['name' => 'Firebase Admin SDK classes loaded', 'status' => 'error',
                             'message' => 'Run: composer require kreait/firebase-php'];
            }
        } catch (Exception $e) {
            $checks[] = ['name' => 'Firebase Admin SDK classes loaded', 'status' => 'error',
                         'message' => $e->getMessage()];
        }
    }
    
    // Check 4: Test Firebase connection
    if (file_exists($autoloadPath) && file_exists($serviceAccountPath)) {
        try {
            require_once __DIR__ . '/firebase_admin_setup.php';
            $orders = FirebaseAdmin::getAllOrders();
            $checks[] = ['name' => 'Firebase connection test', 'status' => 'success',
                        'message' => 'Successfully connected! Found ' . count($orders) . ' order(s)'];
        } catch (Exception $e) {
            $checks[] = ['name' => 'Firebase connection test', 'status' => 'error',
                         'message' => $e->getMessage()];
        }
    }
    
    // Display results
    echo '<h2>Setup Status</h2>';
    echo '<ul>';
    foreach ($checks as $check) {
        $statusClass = $check['status'] === 'success' ? 'success' : 'error';
        echo '<li class="' . $statusClass . '">';
        echo '<strong>' . $check['name'] . ':</strong> ';
        echo '<span class="' . $statusClass . '">' . strtoupper($check['status']) . '</span>';
        if (isset($check['message'])) {
            echo ' - ' . htmlspecialchars($check['message']);
        }
        echo '</li>';
    }
    echo '</ul>';
    
    // Overall status
    $allSuccess = true;
    foreach ($checks as $check) {
        if ($check['status'] !== 'success') {
            $allSuccess = false;
            break;
        }
    }
    
    if ($allSuccess) {
        echo '<h2 class="success">✓ All checks passed! Firebase Admin SDK is ready to use.</h2>';
        echo '<p>You can now access the admin panel and see orders from Firebase.</p>';
    } else {
        echo '<h2 class="error">✗ Some checks failed. Please fix the issues above.</h2>';
        echo '<p>See <a href="FIREBASE_ADMIN_SETUP.md">FIREBASE_ADMIN_SETUP.md</a> for detailed instructions.</p>';
    }
    ?>
    
    <hr>
    <h3>Quick Setup Commands</h3>
    <pre>
# 1. Install Composer dependencies
cd backend/api
composer install

# 2. Download service account key from Firebase Console
# Save as: backend/api/admin/firebase-service-account.json

# 3. Verify setup
# Access this file in browser or run: php check_firebase_setup.php
    </pre>
</body>
</html>

