# Firebase Integration Note

The admin panel is configured to read orders from Firebase Firestore. However, the Firebase REST API requires authentication.

## Current Status

Orders are stored in Firebase Firestore, but the admin panel tries to read them via the REST API which may require authentication.

## Solutions

### Option 1: Allow Public Reads (Development Only - NOT RECOMMENDED FOR PRODUCTION)

Update `firestore.rules` to allow public reads:

```javascript
rules_version = '2';
service cloud.firestore {
  match /databases/{database}/documents {
    match /orders/{document=**} {
      allow read: if true;  // Public read access
      allow write: if request.auth != null;  // Only authenticated users can write
    }
  }
}
```

### Option 2: Use Firebase Admin SDK (Recommended)

1. Download Firebase service account key from Firebase Console
2. Install Firebase Admin SDK for PHP: `composer require kreait/firebase-php`
3. Update `get_orders.php` to use Admin SDK instead of REST API

### Option 3: Sync Orders to MySQL (Recommended for Production)

Modify the Flutter app's `order_service.dart` to also save orders to MySQL when creating them. This way, the admin panel can read from MySQL directly.

## Current Implementation

The current code tries to:
1. First read from MySQL (if orders exist there)
2. If MySQL is empty, try to read from Firebase using REST API
3. If Firebase access fails, it gracefully handles the error

If you're seeing "No orders found", it's likely because:
- Firebase REST API requires authentication
- Firestore security rules don't allow public reads

## Quick Fix for Testing

To test the admin panel with existing orders, you can temporarily allow public reads in Firestore rules (Option 1), or set up Firebase Admin SDK (Option 2).

