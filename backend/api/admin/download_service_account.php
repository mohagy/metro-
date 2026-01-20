<?php
/**
 * Helper script to guide downloading Firebase Service Account Key
 * 
 * Note: Service account keys must be downloaded from Firebase Console
 * This script provides instructions and can verify the key once downloaded
 */

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Download Firebase Service Account Key</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; max-width: 800px; }
        .success { color: green; }
        .error { color: red; }
        .info { background: #e3f2fd; padding: 15px; border-radius: 4px; margin: 10px 0; }
        .step { margin: 20px 0; padding: 15px; background: #f5f5f5; border-radius: 4px; }
        code { background: #f0f0f0; padding: 2px 6px; border-radius: 3px; }
        a { color: #1976d2; }
    </style>
</head>
<body>
    <h1>Firebase Service Account Key Setup</h1>
    
    <?php
    $keyPath = __DIR__ . '/firebase-service-account.json';
    
    if (file_exists($keyPath)) {
        echo '<div class="success">';
        echo '<h2>✓ Service Account Key Found!</h2>';
        echo '<p>The file exists at: <code>' . htmlspecialchars($keyPath) . '</code></p>';
        
        // Validate the JSON
        $jsonContent = file_get_contents($keyPath);
        $jsonData = json_decode($jsonContent, true);
        
        if ($jsonData && isset($jsonData['project_id'])) {
            echo '<p><strong>Project ID:</strong> ' . htmlspecialchars($jsonData['project_id']) . '</p>';
            if (isset($jsonData['client_email'])) {
                echo '<p><strong>Service Account Email:</strong> ' . htmlspecialchars($jsonData['client_email']) . '</p>';
            }
            echo '<p class="success"><strong>✓ JSON is valid!</strong></p>';
            echo '<p><a href="check_firebase_setup.php">Click here to verify full setup</a></p>';
        } else {
            echo '<p class="error"><strong>✗ Invalid JSON file</strong></p>';
        }
        echo '</div>';
    } else {
        echo '<div class="info">';
        echo '<h2>Service Account Key Not Found</h2>';
        echo '<p>The service account key file is not yet downloaded.</p>';
        echo '</div>';
        
        echo '<div class="step">';
        echo '<h3>Step 1: Download Service Account Key</h3>';
        echo '<ol>';
        echo '<li>Go to <a href="https://console.firebase.google.com/project/printing-service-app-8949/settings/serviceaccounts/adminsdk" target="_blank">Firebase Console - Service Accounts</a></li>';
        echo '<li>Click <strong>"Generate New Private Key"</strong></li>';
        echo '<li>A JSON file will be downloaded</li>';
        echo '<li>Save/rename the file as: <code>firebase-service-account.json</code></li>';
        echo '<li>Move it to: <code>' . htmlspecialchars($keyPath) . '</code></li>';
        echo '</ol>';
        echo '</div>';
        
        echo '<div class="step">';
        echo '<h3>Step 2: Verify Setup</h3>';
        echo '<p>After downloading the key, refresh this page or visit:</p>';
        echo '<p><a href="check_firebase_setup.php">check_firebase_setup.php</a></p>';
        echo '</div>';
        
        echo '<div class="info">';
        echo '<h3>⚠️ Security Note</h3>';
        echo '<p>The service account key has full access to your Firebase project. Keep it secure!</p>';
        echo '<ul>';
        echo '<li>Never commit it to version control (already in .gitignore)</li>';
        echo '<li>Restrict file permissions</li>';
        echo '<li>Don\'t share it publicly</li>';
        echo '</ul>';
        echo '</div>';
    }
    ?>
    
    <hr>
    <p><a href="../admin/index.html">← Back to Admin Panel</a></p>
</body>
</html>

