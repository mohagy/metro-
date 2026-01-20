# Firebase Setup Status

## ✅ Completed

1. **Firebase Project Created**: `printing-service-app-8949`
2. **Firestore Database**: Enabled and created
3. **Firestore Rules**: Deployed successfully
4. **Firestore Indexes**: Deployed successfully
5. **Storage Rules**: Created (needs Storage to be enabled)

## ⚠️ Manual Steps Required

### 1. Enable Firebase Storage

1. Go to: https://console.firebase.google.com/project/printing-service-app-8949/storage
2. Click "Get Started"
3. Choose "Start in test mode" (we'll update rules after)
4. Select a location (same as Firestore: us-central1)
5. Click "Done"

After enabling Storage, run:
```bash
firebase deploy --only storage:rules
```

### 2. Enable Authentication

1. Go to: https://console.firebase.google.com/project/printing-service-app-8949/authentication
2. Click "Get Started"
3. Go to "Sign-in method" tab
4. Enable "Email/Password"
5. Click "Save"

### 3. Get Android Configuration

1. Go to: https://console.firebase.google.com/project/printing-service-app-8949/settings/general
2. Scroll to "Your apps" section
3. Click the Android icon (or "Add app" > Android)
4. Enter package name: `com.example.printing_service_app`
   - **Note**: Update this to match your actual package name in `android/app/build.gradle`
5. Download `google-services.json`
6. Place it in: `android/app/google-services.json`

### 4. Get iOS Configuration

1. In the same settings page
2. Click the iOS icon (or "Add app" > iOS)
3. Enter bundle ID: `com.example.printingServiceApp`
   - **Note**: Update this to match your actual bundle ID
4. Download `GoogleService-Info.plist`
5. Place it in: `ios/Runner/GoogleService-Info.plist`

### 5. Update Android Build Files

Add to `android/build.gradle` (project level):
```gradle
buildscript {
    dependencies {
        classpath 'com.google.gms:google-services:4.4.0'
    }
}
```

Add to `android/app/build.gradle` (app level, at the bottom):
```gradle
apply plugin: 'com.google.gms.google-services'
```

## Quick Commands

```bash
# Check current project
firebase use

# Deploy all rules
firebase deploy --only firestore:rules,storage:rules,firestore:indexes

# View project info
firebase projects:list

# Open Firebase Console
firebase open
```

## Project Details

- **Project ID**: `printing-service-app-8949`
- **Firestore Location**: `us-central1`
- **Console URL**: https://console.firebase.google.com/project/printing-service-app-8949/overview

## Next Steps

1. Complete the manual steps above
2. Update package name/bundle ID in Firebase Console if needed
3. Download and place configuration files
4. Update Android build files
5. Run `flutter pub get` and `flutter run`

