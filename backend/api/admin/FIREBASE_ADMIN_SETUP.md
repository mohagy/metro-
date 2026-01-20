# Firebase Admin SDK Setup Guide

This guide will help you set up Firebase Admin SDK for PHP to enable the admin panel to read orders from Firebase Firestore.

## Prerequisites

1. **Composer** - PHP dependency manager
   - Download from: https://getcomposer.org/download/
   - Or use the installer:
     ```bash
     php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
     php composer-setup.php
     php -r "unlink('composer-setup.php');"
     ```

## Step 1: Install Composer Dependencies

1. Navigate to the `backend/api` directory:
   ```bash
   cd backend/api
   ```

2. Install dependencies:
   ```bash
   composer install
   ```

   This will install the Firebase Admin SDK and create a `vendor` directory.

## Step 2: Get Firebase Service Account Key

1. Go to [Firebase Console](https://console.firebase.google.com/)
2. Select your project: **printing-service-app-8949**
3. Click the gear icon (⚙️) next to "Project Overview"
4. Select **Project Settings**
5. Go to the **Service Accounts** tab
6. Click **Generate New Private Key**
7. A JSON file will be downloaded - this is your service account key
8. **Save this file** as: `backend/api/admin/firebase-service-account.json`

   ⚠️ **IMPORTANT**: Keep this file secure! It has full access to your Firebase project.
   - Never commit it to version control
   - Add it to `.gitignore`
   - Restrict file permissions (chmod 600 on Linux/Mac)

## Step 3: Verify Setup

1. Check that the service account file exists:
   ```bash
   ls backend/api/admin/firebase-service-account.json
   ```

2. Check that Composer dependencies are installed:
   ```bash
   ls backend/api/vendor
   ```

## Step 4: Test the Integration

1. Access the admin panel: `http://localhost/metro/backend/admin/index.html`
2. Login with: `admin` / `admin123`
3. Go to the **Orders** tab
4. You should now see orders from Firebase Firestore!

## Troubleshooting

### Error: "Firebase service account key not found"
- Make sure the file is saved as: `backend/api/admin/firebase-service-account.json`
- Check the file path is correct
- Verify file permissions (should be readable by PHP)

### Error: "Failed to initialize Firebase"
- Check that the service account JSON file is valid
- Verify the file contains proper JSON structure
- Make sure the project ID matches: `printing-service-app-8949`

### Error: "Class 'Kreait\Firebase\Factory' not found"
- Run `composer install` in the `backend/api` directory
- Check that `vendor/autoload.php` exists
- Verify Composer is installed correctly

### Orders still not showing
- Check PHP error logs for detailed error messages
- Verify Firestore rules allow reads (they should with Admin SDK)
- Test Firebase connection by checking the service account key is valid

## Security Notes

1. **Never commit the service account key to version control**
   - Add to `.gitignore`:
     ```
     backend/api/admin/firebase-service-account.json
     ```

2. **Restrict file permissions** (Linux/Mac):
   ```bash
   chmod 600 backend/api/admin/firebase-service-account.json
   ```

3. **In production**, consider:
   - Using environment variables for the service account path
   - Storing the key in a secure location outside the web root
   - Using a secrets management service

## File Structure

After setup, your directory should look like:
```
backend/api/
├── admin/
│   ├── firebase-service-account.json  ← Service account key (DO NOT COMMIT)
│   ├── firebase_admin_setup.php       ← Firebase Admin SDK wrapper
│   └── ...
├── vendor/                            ← Composer dependencies
│   └── autoload.php
└── composer.json
```

## Next Steps

Once Firebase Admin SDK is set up:
- Orders from Firebase will automatically appear in the admin panel
- Order status updates will sync to both MySQL and Firebase
- The admin panel will work seamlessly with your existing Firebase setup

