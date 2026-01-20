import 'package:flutter/foundation.dart' show kIsWeb;

class ApiConfig {
  // Production API base URL
  // Update this to your production backend URL
  static String get baseUrl {
    if (kIsWeb) {
      // For GitHub Pages deployment, you'll need to host your PHP backend elsewhere
      // Options: 
      // 1. Use a service like Heroku, Railway, or Render for PHP
      // 2. Use your own server
      // 3. Use Firebase Functions or Cloud Functions
      return 'https://your-backend-domain.com';
    }
    return 'http://localhost';
  }
  
  // Project folder name
  static const String projectPath = '/metro';
  static const String apiPath = '/backend/api';
  
  static String get apiBaseUrl => '$baseUrl$projectPath$apiPath';
  
  // API endpoints
  static String get uploadFileUrl => '$apiBaseUrl/upload_file.php';
  static String get deleteFileUrl => '$apiBaseUrl/delete_file.php';
  static String getFileUrl(String fileId) => '$apiBaseUrl/get_file.php?id=$fileId';
}

