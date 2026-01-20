# Firebase CLI Setup Guide

## Step 1: Select or Create Firebase Project

Run this command to see your existing projects:
```bash
firebase projects:list
```

If you need to create a new project, go to [Firebase Console](https://console.firebase.google.com/) and create one.

## Step 2: Link Your Project

Run this command to link your local project to Firebase:
```bash
firebase use --add
```

Select your project from the list, and give it an alias (you can use "default").

Or if you know your project ID, update `.firebaserc`:
```json
{
  "projects": {
    "default": "your-actual-project-id"
  }
}
```

## Step 3: Initialize Firebase Services

### Option A: Use the Interactive Setup (Recommended for first time)

Run:
```bash
firebase init
```

Select these services:
- **Firestore**: Set up Firestore security rules and indexes
- **Storage**: Set up Storage security rules
- **Functions**: (Optional) If you want Cloud Functions later
- **Hosting**: (Optional) If you want web hosting

### Option B: Deploy Rules Manually

The rules files are already created:
- `firestore.rules` - Firestore security rules
- `storage.rules` - Storage security rules
- `firestore.indexes.json` - Firestore indexes

Deploy them:
```bash
firebase deploy --only firestore:rules
firebase deploy --only storage:rules
firebase deploy --only firestore:indexes
```

## Step 4: Enable Services in Firebase Console

1. Go to [Firebase Console](https://console.firebase.google.com/)
2. Select your project
3. Enable these services:
   - **Authentication**: Go to Authentication > Sign-in method > Enable Email/Password
   - **Firestore Database**: Already enabled if you deployed rules
   - **Storage**: Already enabled if you deployed rules
   - **Cloud Messaging**: Will be enabled automatically when needed

## Step 5: Get Configuration Files

### For Android

1. In Firebase Console, go to Project Settings (gear icon)
2. Under "Your apps", click Android icon
3. Enter package name: `com.example.printing_service_app` (or your package name)
4. Download `google-services.json`
5. Place it in: `android/app/google-services.json`

### For iOS

1. In Firebase Console, go to Project Settings
2. Under "Your apps", click iOS icon
3. Enter bundle ID: `com.example.printingServiceApp` (or your bundle ID)
4. Download `GoogleService-Info.plist`
5. Place it in: `ios/Runner/GoogleService-Info.plist`

## Step 6: Update Android Configuration

Add to `android/build.gradle` (project level):
```gradle
buildscript {
    dependencies {
        // Add this line
        classpath 'com.google.gms:google-services:4.4.0'
    }
}
```

Add to `android/app/build.gradle` (app level):
```gradle
// Add at the bottom
apply plugin: 'com.google.gms.google-services'
```

## Step 7: Verify Setup

Run Flutter app:
```bash
flutter pub get
flutter run
```

## Quick Commands Reference

```bash
# List all Firebase projects
firebase projects:list

# Use a specific project
firebase use <project-id>

# Deploy Firestore rules
firebase deploy --only firestore:rules

# Deploy Storage rules
firebase deploy --only storage:rules

# Deploy Firestore indexes
firebase deploy --only firestore:indexes

# Deploy everything
firebase deploy

# View Firestore data
firebase firestore:indexes

# Open Firebase Console
firebase open
```

## Troubleshooting

### Error: "Firebase project not found"
- Make sure you're logged in: `firebase login`
- Check your project ID in `.firebaserc`
- Verify project exists: `firebase projects:list`

### Error: "Permission denied"
- Make sure you have owner/editor access to the Firebase project
- Check Firebase Console > IAM & Admin

### Configuration files not found
- Make sure `google-services.json` is in `android/app/`
- Make sure `GoogleService-Info.plist` is in `ios/Runner/`

