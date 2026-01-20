<?php
/**
 * Firebase REST API Client using Service Account Authentication
 * This works without requiring gRPC extension
 */

class FirebaseRestClient {
    private static $accessToken = null;
    private static $tokenExpiry = null;
    private static $projectId = null;
    
    /**
     * Get access token from service account
     */
    private static function getAccessToken() {
        // Return cached token if still valid
        if (self::$accessToken && self::$tokenExpiry && time() < self::$tokenExpiry) {
            return self::$accessToken;
        }
        
        $serviceAccountPath = __DIR__ . '/firebase-service-account.json';
        if (!file_exists($serviceAccountPath)) {
            throw new Exception('Service account key not found');
        }
        
        $serviceAccount = json_decode(file_get_contents($serviceAccountPath), true);
        self::$projectId = $serviceAccount['project_id'];
        
        // Create JWT for service account
        $now = time();
        $jwtHeader = [
            'alg' => 'RS256',
            'typ' => 'JWT'
        ];
        
        $jwtPayload = [
            'iss' => $serviceAccount['client_email'],
            'scope' => 'https://www.googleapis.com/auth/datastore',
            'aud' => 'https://oauth2.googleapis.com/token',
            'exp' => $now + 3600,
            'iat' => $now
        ];
        
        // Sign JWT (simplified - in production use a proper JWT library)
        // For now, we'll use Google Auth library if available, or use a simpler approach
        
        // Use Google Auth library to get access token
        if (class_exists('Google\Auth\Credentials\ServiceAccountCredentials')) {
            $credentials = new \Google\Auth\Credentials\ServiceAccountCredentials(
                'https://www.googleapis.com/auth/datastore',
                $serviceAccount
            );
            
            $token = $credentials->fetchAuthToken();
            self::$accessToken = $token['access_token'];
            self::$tokenExpiry = $now + 3600;
            
            return self::$accessToken;
        }
        
        // Fallback: Use JWT to get access token
        // This is a simplified version - in production, use a proper JWT library
        require_once dirname(__DIR__) . '/vendor/autoload.php';
        
        if (class_exists('Google\Auth\Credentials\ServiceAccountCredentials')) {
            $credentials = new \Google\Auth\Credentials\ServiceAccountCredentials(
                'https://www.googleapis.com/auth/datastore',
                $serviceAccount
            );
            
            $token = $credentials->fetchAuthToken();
            self::$accessToken = $token['access_token'];
            self::$tokenExpiry = $now + 3600;
            
            return self::$accessToken;
        }
        
        throw new Exception('Google Auth library not available. Please ensure google/auth is installed via Composer.');
    }
    
    /**
     * Get all documents from a collection
     */
    public static function getCollection($collectionName) {
        $token = self::getAccessToken();
        $projectId = self::$projectId;
        
        $url = "https://firestore.googleapis.com/v1/projects/{$projectId}/databases/(default)/documents/{$collectionName}";
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);
        
        if ($httpCode !== 200) {
            throw new Exception("Firebase REST API error: HTTP {$httpCode}. Response: " . substr($response, 0, 200));
        }
        
        $data = json_decode($response, true);
        
        if (!isset($data['documents'])) {
            return [];
        }
        
        $documents = [];
        foreach ($data['documents'] as $doc) {
            $docId = basename($doc['name']);
            $fields = $doc['fields'];
            
            // Convert Firestore format to array
            $document = ['id' => $docId, 'order_id' => $docId];
            foreach ($fields as $key => $value) {
                $document[$key] = self::extractFirestoreValue($value);
            }
            
            $documents[] = $document;
        }
        
        return $documents;
    }
    
    /**
     * Extract value from Firestore field format
     */
    private static function extractFirestoreValue($field) {
        if (isset($field['stringValue'])) {
            return $field['stringValue'];
        } elseif (isset($field['integerValue'])) {
            return (int)$field['integerValue'];
        } elseif (isset($field['doubleValue'])) {
            return (float)$field['doubleValue'];
        } elseif (isset($field['booleanValue'])) {
            return $field['booleanValue'];
        } elseif (isset($field['timestampValue'])) {
            return $field['timestampValue'];
        } elseif (isset($field['mapValue'])) {
            $result = [];
            foreach ($field['mapValue']['fields'] as $key => $value) {
                $result[$key] = self::extractFirestoreValue($value);
            }
            return $result;
        } elseif (isset($field['arrayValue'])) {
            $result = [];
            foreach ($field['arrayValue']['values'] as $value) {
                $result[] = self::extractFirestoreValue($value);
            }
            return $result;
        }
        return null;
    }
    
    /**
     * Get a single document
     */
    public static function getDocument($collectionName, $documentId) {
        $token = self::getAccessToken();
        $projectId = self::$projectId;
        
        $url = "https://firestore.googleapis.com/v1/projects/{$projectId}/databases/(default)/documents/{$collectionName}/{$documentId}";
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 404) {
            return null;
        }
        
        if ($httpCode !== 200) {
            throw new Exception("Firebase REST API error: HTTP {$httpCode}");
        }
        
        $data = json_decode($response, true);
        
        if (!isset($data['fields'])) {
            return null;
        }
        
        $document = ['id' => $documentId, 'order_id' => $documentId];
        foreach ($data['fields'] as $key => $value) {
            $document[$key] = self::extractFirestoreValue($value);
        }
        
        return $document;
    }
    
    /**
     * Update a document field
     */
    public static function updateDocument($collectionName, $documentId, $updates) {
        $token = self::getAccessToken();
        $projectId = self::$projectId;
        
        $url = "https://firestore.googleapis.com/v1/projects/{$projectId}/databases/(default)/documents/{$collectionName}/{$documentId}?updateMask.fieldPaths=" . implode('&updateMask.fieldPaths=', array_keys($updates));
        
        // Convert updates to Firestore format
        $fields = [];
        foreach ($updates as $key => $value) {
            if (is_string($value)) {
                $fields[$key] = ['stringValue' => $value];
            } elseif (is_int($value)) {
                $fields[$key] = ['integerValue' => (string)$value];
            } elseif (is_float($value)) {
                $fields[$key] = ['doubleValue' => $value];
            } elseif (is_bool($value)) {
                $fields[$key] = ['booleanValue' => $value];
            } elseif ($value instanceof \DateTime) {
                $fields[$key] = ['timestampValue' => $value->format('Y-m-d\TH:i:s\Z')];
            }
        }
        
        $payload = ['fields' => $fields];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 200) {
            throw new Exception("Firebase REST API error: HTTP {$httpCode}. Response: " . substr($response, 0, 200));
        }
        
        return true;
    }
}

