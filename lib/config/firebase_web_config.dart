import 'package:firebase_core/firebase_core.dart';
import 'package:flutter/foundation.dart';
import 'firebase_options.dart' if (dart.library.html) '../main.dart';

// Firebase configuration for web
FirebaseOptions getFirebaseOptions() {
  if (kIsWeb) {
    return FirebaseOptions(
      apiKey: FirebaseOptions.apiKey,
      appId: FirebaseOptions.appId,
      messagingSenderId: FirebaseOptions.messagingSenderId,
      projectId: FirebaseOptions.projectId,
      authDomain: FirebaseOptions.authDomain,
      storageBucket: FirebaseOptions.storageBucket,
    );
  }
  
  // For mobile platforms, return default options
  // This will be initialized from platform-specific files
  return const FirebaseOptions(
    apiKey: '',
    appId: '',
    messagingSenderId: '',
    projectId: '',
  );
}

