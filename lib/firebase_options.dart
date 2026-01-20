// File generated using Firebase web configuration
// This file contains Firebase configuration for web platform

import 'package:firebase_core/firebase_core.dart' show FirebaseOptions;

// Firebase configuration class
class FirebaseWebOptions {
  static const String apiKey = "AIzaSyC3Er5dy-dszTP3swA7_frXMV_M5iwAyZg";
  static const String authDomain = "printing-service-app-8949.firebaseapp.com";
  static const String projectId = "printing-service-app-8949";
  static const String storageBucket = "printing-service-app-8949.firebasestorage.app";
  static const String messagingSenderId = "202313864742";
  static const String appId = "1:202313864742:web:f5185ad2a864f5fa95f8e7";

  // Get FirebaseOptions for web
  static FirebaseOptions get options => FirebaseOptions(
    apiKey: apiKey,
    appId: appId,
    messagingSenderId: messagingSenderId,
    projectId: projectId,
    authDomain: authDomain,
    storageBucket: storageBucket,
  );

  static Map<String, dynamic> get webConfig => {
    'apiKey': apiKey,
    'authDomain': authDomain,
    'projectId': projectId,
    'storageBucket': storageBucket,
    'messagingSenderId': messagingSenderId,
    'appId': appId,
  };
}

