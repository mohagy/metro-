# Firebase Setup Instructions

## 1. Create Firebase Project

1. Go to [Firebase Console](https://console.firebase.google.com/)
2. Click "Add project"
3. Follow the setup wizard

## 2. Configure Android

1. In Firebase Console, click the Android icon to add an Android app
2. Package name: `com.example.printing_service_app` (or your package name)
3. Download `google-services.json`
4. Place it in `android/app/google-services.json`
5. Add to `android/build.gradle`:
```gradle
buildscript {
    dependencies {
        classpath 'com.google.gms:google-services:4.4.0'
    }
}
```
6. Add to `android/app/build.gradle`:
```gradle
apply plugin: 'com.google.gms.google-services'
```

## 3. Configure iOS

1. In Firebase Console, click the iOS icon to add an iOS app
2. Bundle ID: `com.example.printingServiceApp` (or your bundle ID)
3. Download `GoogleService-Info.plist`
4. Place it in `ios/Runner/GoogleService-Info.plist`
5. Add to `ios/Runner/Info.plist`:
```xml
<key>FirebaseAppDelegateProxyEnabled</key>
<false/>
```

## 4. Enable Firebase Services

### Authentication
1. Go to Authentication > Sign-in method
2. Enable Email/Password

### Firestore Database
1. Go to Firestore Database
2. Click "Create database"
3. Start in test mode (update rules later for production)
4. Choose a location

### Cloud Storage
1. Go to Storage
2. Click "Get started"
3. Start in test mode (update rules later for production)

### Cloud Messaging
1. Go to Cloud Messaging
2. Follow setup instructions

## 5. Security Rules

### Firestore Rules
```javascript
rules_version = '2';
service cloud.firestore {
  match /databases/{database}/documents {
    match /users/{userId} {
      allow read, write: if request.auth != null && request.auth.uid == userId;
    }
    match /orders/{orderId} {
      allow read, write: if request.auth != null && 
        (request.auth.uid == resource.data.userId || 
         request.auth.uid == request.resource.data.userId);
    }
  }
}
```

### Storage Rules
```javascript
rules_version = '2';
service firebase.storage {
  match /b/{bucket}/o {
    match /uploads/{userId}/{orderId}/{fileName} {
      allow read, write: if request.auth != null && request.auth.uid == userId;
    }
  }
}
```

## 6. Initialize Firebase in Flutter

The app already initializes Firebase in `main.dart`. Make sure you have:
- `firebase_core` package installed
- Firebase configuration files in place
- Run `flutter pub get`

