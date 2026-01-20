import 'package:flutter/foundation.dart' show kIsWeb;

class ApiConfig {
  // Local PHP API base URL
  // IMPORTANT: Make sure XAMPP is running before testing!
  // For Web: XAMPP should be running on http://localhost (port 80)
  // For Android Emulator, use: 'http://10.0.2.2'
  // For iOS Simulator, use: 'http://localhost'
  // For physical device, use your computer's IP: 'http://192.168.1.x'
  static String get baseUrl {
    // Always use localhost for the backend API
    // Flutter web runs on a different port, but backend is on port 80
    return 'http://localhost';
  }
  
  // Project folder name in htdocs
  static const String projectPath = '/metro';
  static const String apiPath = '/backend/api';
  
  static String get apiBaseUrl => '$baseUrl$projectPath$apiPath';
  
  // API endpoints
  static String get uploadFileUrl => '$apiBaseUrl/upload_file.php';
  static String get deleteFileUrl => '$apiBaseUrl/delete_file.php';
  static String getFileUrl(String fileId) => '$apiBaseUrl/get_file.php?id=$fileId';
}

