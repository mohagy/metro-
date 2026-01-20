<?php
/**
 * Firebase Admin SDK Setup
 * 
 * This file initializes the Firebase Admin SDK for use in admin endpoints.
 * 
 * SETUP INSTRUCTIONS:
 * 1. Install Composer (if not installed):
 *    - Download from https://getcomposer.org/download/
 *    - Or use: php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
 * 
 * 2. Install Firebase Admin SDK:
 *    cd backend/api
 *    composer install
 * 
 * 3. Get Firebase Service Account Key:
 *    - Go to Firebase Console: https://console.firebase.google.com/
 *    - Select your project: printing-service-app-8949
 *    - Go to Project Settings > Service Accounts
 *    - Click "Generate New Private Key"
 *    - Save the JSON file as: backend/api/admin/firebase-service-account.json
 * 
 * 4. Update the path below if you saved the key file elsewhere
 */

// Load Composer autoload - try multiple path resolutions
$autoloadPaths = [
    __DIR__ . '/../../vendor/autoload.php',
    dirname(__DIR__) . '/vendor/autoload.php',
    dirname(dirname(__DIR__)) . '/vendor/autoload.php',
];

$autoloadLoaded = false;
foreach ($autoloadPaths as $autoloadPath) {
    $resolved = realpath($autoloadPath);
    if ($resolved && file_exists($resolved)) {
        require_once $resolved;
        $autoloadLoaded = true;
        break;
    }
}

if (!$autoloadLoaded) {
    throw new Exception('Composer autoload file not found. Run: cd backend/api && composer install');
}

use Kreait\Firebase\Factory;
use Kreait\Firebase\ServiceAccount;
use Google\Cloud\Firestore\FirestoreClient;

class FirebaseAdmin {
    private static $firestore = null;
    private static $factory = null;
    
    /**
     * Initialize Firebase Admin SDK
     */
    public static function init() {
        if (self::$factory !== null) {
            return self::$factory;
        }
        
        $serviceAccountPath = __DIR__ . '/firebase-service-account.json';
        
        if (!file_exists($serviceAccountPath)) {
            throw new Exception(
                'Firebase service account key not found at: ' . $serviceAccountPath . "\n" .
                'Please download it from Firebase Console and save it as firebase-service-account.json'
            );
        }
        
        try {
            // Set environment variable to use REST instead of gRPC (if gRPC extension is not available)
            if (!extension_loaded('grpc')) {
                putenv('GOOGLE_CLOUD_USE_GRPC=false');
            }
            
            self::$factory = (new Factory)
                ->withServiceAccount($serviceAccountPath);
            
            return self::$factory;
        } catch (Exception $e) {
            throw new Exception('Failed to initialize Firebase: ' . $e->getMessage());
        }
    }
    
    /**
     * Get Firestore instance
     */
    public static function getFirestore() {
        if (self::$firestore === null) {
            $factory = self::init();
            
            // Configure to use REST transport if gRPC is not available
            if (!extension_loaded('grpc')) {
                // Use REST transport
                $serviceAccountPath = __DIR__ . '/firebase-service-account.json';
                $serviceAccount = json_decode(file_get_contents($serviceAccountPath), true);
                
                self::$firestore = new FirestoreClient([
                    'projectId' => $serviceAccount['project_id'],
                    'keyFilePath' => $serviceAccountPath,
                    'transport' => 'rest', // Force REST transport
                ]);
            } else {
                self::$firestore = $factory->createFirestore();
            }
        }
        
        return self::$firestore;
    }
    
    /**
     * Get all orders from Firestore
     */
    public static function getAllOrders() {
        try {
            // Try using Firestore client first (if gRPC is available)
            if (extension_loaded('grpc')) {
                $firestore = self::getFirestore();
                $collection = $firestore->collection('orders');
                $documents = $collection->documents();
                
                $orders = [];
                foreach ($documents as $document) {
                    if ($document->exists()) {
                        $data = $document->data();
                        $data['id'] = $document->id();
                        $data['order_id'] = $document->id();
                        $orders[] = $data;
                    }
                }
                
                return $orders;
            } else {
                // Fallback to REST API
                require_once __DIR__ . '/firebase_rest_client.php';
                return FirebaseRestClient::getCollection('orders');
            }
        } catch (Exception $e) {
            // If Firestore client fails, try REST API
            try {
                require_once __DIR__ . '/firebase_rest_client.php';
                return FirebaseRestClient::getCollection('orders');
            } catch (Exception $e2) {
                throw new Exception('Failed to fetch orders from Firestore: ' . $e->getMessage() . ' / ' . $e2->getMessage());
            }
        }
    }
    
    /**
     * Get a single order by ID
     */
    public static function getOrder($orderId) {
        try {
            // Try using Firestore client first (if gRPC is available)
            if (extension_loaded('grpc')) {
                $firestore = self::getFirestore();
                $document = $firestore->collection('orders')->document($orderId);
                $snapshot = $document->snapshot();
                
                if (!$snapshot->exists()) {
                    return null;
                }
                
                $data = $snapshot->data();
                $data['id'] = $snapshot->id();
                $data['order_id'] = $snapshot->id();
                
                return $data;
            } else {
                // Fallback to REST API
                require_once __DIR__ . '/firebase_rest_client.php';
                return FirebaseRestClient::getDocument('orders', $orderId);
            }
        } catch (Exception $e) {
            // If Firestore client fails, try REST API
            try {
                require_once __DIR__ . '/firebase_rest_client.php';
                return FirebaseRestClient::getDocument('orders', $orderId);
            } catch (Exception $e2) {
                throw new Exception('Failed to fetch order from Firestore: ' . $e->getMessage() . ' / ' . $e2->getMessage());
            }
        }
    }
    
    /**
     * Update order status in Firestore
     */
    public static function updateOrderStatus($orderId, $status) {
        try {
            // Try using Firestore client first (if gRPC is available)
            if (extension_loaded('grpc')) {
                $firestore = self::getFirestore();
                $document = $firestore->collection('orders')->document($orderId);
                
                $updateData = ['status' => $status];
                
                if ($status === 'Completed') {
                    $updateData['completedAt'] = new \DateTime();
                }
                
                $document->update($updateData);
                
                return true;
            } else {
                // Fallback to REST API
                require_once __DIR__ . '/firebase_rest_client.php';
                $updates = ['status' => $status];
                if ($status === 'Completed') {
                    $updates['completedAt'] = new \DateTime();
                }
                return FirebaseRestClient::updateDocument('orders', $orderId, $updates);
            }
        } catch (Exception $e) {
            // If Firestore client fails, try REST API
            try {
                require_once __DIR__ . '/firebase_rest_client.php';
                $updates = ['status' => $status];
                if ($status === 'Completed') {
                    $updates['completedAt'] = new \DateTime();
                }
                return FirebaseRestClient::updateDocument('orders', $orderId, $updates);
            } catch (Exception $e2) {
                throw new Exception('Failed to update order status in Firestore: ' . $e->getMessage() . ' / ' . $e2->getMessage());
            }
        }
    }
    
    /**
     * Convert Firestore data to admin panel format
     */
    public static function formatOrderForAdmin($firestoreOrder) {
        $order = [
            'id' => $firestoreOrder['id'] ?? $firestoreOrder['order_id'] ?? '',
            'order_id' => $firestoreOrder['order_id'] ?? $firestoreOrder['id'] ?? '',
            'user_id' => $firestoreOrder['userId'] ?? '',
            'status' => $firestoreOrder['status'] ?? 'Pending',
            'total_cost' => (float)($firestoreOrder['totalCost'] ?? 0),
            'delivery_option' => $firestoreOrder['deliveryOption'] ?? '',
            'qr_code' => $firestoreOrder['qrCode'] ?? '',
        ];
        
        // Handle timestamps
        if (isset($firestoreOrder['createdAt'])) {
            $createdAt = $firestoreOrder['createdAt'];
            if ($createdAt instanceof \DateTime) {
                $order['created_at'] = $createdAt->format('Y-m-d H:i:s');
            } elseif (is_string($createdAt)) {
                try {
                    $date = new \DateTime($createdAt);
                    $order['created_at'] = $date->format('Y-m-d H:i:s');
                } catch (Exception $e) {
                    $order['created_at'] = date('Y-m-d H:i:s');
                }
            }
        } else {
            $order['created_at'] = date('Y-m-d H:i:s');
        }
        
        if (isset($firestoreOrder['estimatedReady'])) {
            $estimatedReady = $firestoreOrder['estimatedReady'];
            if ($estimatedReady instanceof \DateTime) {
                $order['estimated_ready'] = $estimatedReady->format('Y-m-d H:i:s');
            } elseif (is_string($estimatedReady)) {
                try {
                    $date = new \DateTime($estimatedReady);
                    $order['estimated_ready'] = $date->format('Y-m-d H:i:s');
                } catch (Exception $e) {
                    $order['estimated_ready'] = null;
                }
            }
        }
        
        if (isset($firestoreOrder['completedAt'])) {
            $completedAt = $firestoreOrder['completedAt'];
            if ($completedAt instanceof \DateTime) {
                $order['completed_at'] = $completedAt->format('Y-m-d H:i:s');
            } elseif (is_string($completedAt)) {
                try {
                    $date = new \DateTime($completedAt);
                    $order['completed_at'] = $date->format('Y-m-d H:i:s');
                } catch (Exception $e) {
                    $order['completed_at'] = null;
                }
            }
        }
        
        // Extract print options
        if (isset($firestoreOrder['printOption']) && is_array($firestoreOrder['printOption'])) {
            $printOption = $firestoreOrder['printOption'];
            $order['print_options'] = [
                'paper_size' => $printOption['paperSize'] ?? '',
                'color' => $printOption['color'] ?? '',
                'quantity' => (int)($printOption['quantity'] ?? 1),
                'sides' => $printOption['sides'] ?? '',
                'orientation' => $printOption['orientation'] ?? '',
                'binding' => $printOption['binding'] ?? '',
            ];
        }
        
        // Extract files
        if (isset($firestoreOrder['files']) && is_array($firestoreOrder['files'])) {
            $order['files'] = [];
            foreach ($firestoreOrder['files'] as $file) {
                if (is_array($file)) {
                    $order['files'][] = [
                        'id' => $file['id'] ?? '',
                        'name' => $file['name'] ?? '',
                        'original_name' => $file['name'] ?? '',
                        'size_bytes' => (int)($file['sizeBytes'] ?? 0),
                        'file_type' => $file['fileType'] ?? '',
                        'file_url' => $file['firebaseStorageUrl'] ?? $file['fileUrl'] ?? '',
                    ];
                }
            }
        }
        
        return $order;
    }
}

