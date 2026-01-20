# Firebase Storage CORS Fix

## Problem
You're getting CORS errors when trying to upload files to Firebase Storage from the web app:
```
Access to XMLHttpRequest at 'https://firebasestorage.googleapis.com/...' 
from origin 'http://localhost:56839' has been blocked by CORS policy
```

## Solution

Firebase Storage requires CORS to be configured on the bucket. Here's how to fix it:

### Option 1: Configure CORS via Firebase Console (Recommended)

1. Go to [Firebase Console](https://console.firebase.google.com/)
2. Select your project: `printing-service-app-8949`
3. Go to **Storage** in the left menu
4. Click on the **Rules** tab
5. Make sure your storage rules are deployed (they should match `storage.rules` in your project)
6. Go to the **Files** tab
7. Click on the **Settings** (gear icon) → **CORS configuration**
8. Add the following CORS configuration:

```json
[
  {
    "origin": ["*"],
    "method": ["GET", "POST", "PUT", "DELETE", "HEAD", "OPTIONS"],
    "maxAgeSeconds": 3600,
    "responseHeader": ["Content-Type", "Authorization", "Content-Length", "User-Agent", "x-goog-resumable"]
  }
]
```

**Note**: For production, replace `"*"` with your specific domain(s).

### Option 2: Configure CORS via gsutil (Command Line)

If you have `gsutil` installed:

```bash
# Create a CORS configuration file (cors.json)
cat > cors.json << EOF
[
  {
    "origin": ["*"],
    "method": ["GET", "POST", "PUT", "DELETE", "HEAD", "OPTIONS"],
    "maxAgeSeconds": 3600,
    "responseHeader": ["Content-Type", "Authorization", "Content-Length", "User-Agent", "x-goog-resumable"]
  }
]
EOF

# Apply CORS configuration
gsutil cors set cors.json gs://printing-service-app-8949.firebasestorage.app
```

### Option 3: Use Firebase CLI

```bash
# Install Firebase CLI if not already installed
npm install -g firebase-tools

# Login to Firebase
firebase login

# Set CORS using Firebase CLI (if supported)
# Note: This might require using gsutil instead
```

## Verify CORS is Configured

After configuring CORS, test the upload again. The CORS error should be resolved.

## Alternative: Use PHP Backend for Web (Temporary Workaround)

If you can't configure CORS immediately, you can temporarily use the PHP backend for web uploads by modifying `lib/services/file_upload_service.dart`:

Change:
```dart
if (kIsWeb) {
  return await _uploadToFirebaseStorage(...);
}
```

To:
```dart
if (kIsWeb) {
  // Temporarily use PHP backend until CORS is configured
  return await _uploadToPhpBackend(...);
}
```

But you'll need XAMPP running for this to work.

## After Fixing CORS

Once CORS is configured, your Firebase Storage uploads should work correctly on web without needing XAMPP.

