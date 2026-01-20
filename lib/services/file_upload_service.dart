import 'dart:io';
import 'dart:async';
import 'dart:typed_data';
import 'package:http/http.dart' as http;
import 'package:file_picker/file_picker.dart';
import 'dart:convert';
import 'package:flutter/foundation.dart' show kIsWeb;
import 'package:firebase_storage/firebase_storage.dart';
import 'package:firebase_auth/firebase_auth.dart';
import '../models/print_file.dart';
import '../config/app_config.dart';
import '../config/api_config.dart';

class FileUploadService {

  Future<List<PrintFile>> pickFiles() async {
    try {
      FilePickerResult? result = await FilePicker.platform.pickFiles(
        type: FileType.custom,
        allowedExtensions: AppConfig.supportedFileTypes,
        allowMultiple: true,
      );

      if (result != null) {
        List<PrintFile> files = [];
        for (var platformFile in result.files) {
          int fileSize;
          String? path;
          Uint8List? bytes;
          
          if (kIsWeb) {
            // On web, use bytes
            bytes = platformFile.bytes;
            if (bytes == null) {
              continue; // Skip files without bytes
            }
            fileSize = bytes.length;
          } else {
            // On mobile/desktop, use path
            if (platformFile.path == null) {
              continue; // Skip files without path
            }
            path = platformFile.path;
            final file = File(path!);
            fileSize = await file.length();
          }
          
          // Check file size
          if (fileSize > AppConfig.maxFileSizeMB * 1024 * 1024) {
            throw Exception(
                'File ${platformFile.name} exceeds ${AppConfig.maxFileSizeMB}MB limit');
          }

          final fileType = platformFile.extension ?? '';
          final printFile = PrintFile(
            id: DateTime.now().millisecondsSinceEpoch.toString() +
                platformFile.name,
            name: platformFile.name,
            path: path,
            bytes: bytes,
            sizeBytes: fileSize,
            fileType: fileType,
          );
          files.add(printFile);
        }
        return files;
      }
      return [];
    } catch (e) {
      rethrow;
    }
  }

  Future<String> uploadFile(
    PrintFile printFile,
    String userId,
    String orderId,
    Function(double)? onProgress,
  ) async {
    try {
      // Always use PHP backend for storage (XAMPP/phpMyAdmin)
      return await _uploadToPhpBackend(
        printFile,
        userId,
        orderId,
        onProgress,
      );
    } catch (e) {
      rethrow;
    }
  }

  Future<String> _uploadToFirebaseStorage(
    PrintFile printFile,
    String userId,
    String orderId,
    Function(double)? onProgress,
  ) async {
    try {
      // Verify user is authenticated
      final currentUser = FirebaseAuth.instance.currentUser;
      if (currentUser == null) {
        throw Exception('User not authenticated. Please log in first.');
      }
      
      // Verify userId matches authenticated user
      if (currentUser.uid != userId) {
        throw Exception('User ID mismatch. Authenticated as ${currentUser.uid}, but trying to upload as $userId');
      }

      // Refresh auth token to ensure it's valid
      try {
        await currentUser.getIdToken(true); // Force refresh
      } catch (e) {
        print('Warning: Could not refresh auth token: $e');
      }

      if (printFile.bytes == null) {
        throw Exception('File bytes not available for upload');
      }

      if (printFile.bytes!.isEmpty) {
        throw Exception('File is empty');
      }

      final storage = FirebaseStorage.instance;
      
      // Create a unique file name
      final timestamp = DateTime.now().millisecondsSinceEpoch;
      final safeFileName = printFile.name.replaceAll(RegExp(r'[^a-zA-Z0-9._-]'), '_');
      final fileName = '${timestamp}_$safeFileName';
      
      // Create storage reference
      final storageRef = storage.ref().child('uploads/$userId/$orderId/$fileName');
      
      // Notify that upload is starting
      if (onProgress != null) {
        onProgress(0.0);
      }

      // Upload file with metadata
      final uploadTask = storageRef.putData(
        printFile.bytes!,
        SettableMetadata(
          contentType: _getContentType(printFile.fileType),
          customMetadata: {
            'originalName': printFile.name,
            'fileType': printFile.fileType,
            'sizeBytes': printFile.sizeBytes.toString(),
          },
        ),
      );

      // Track upload progress using a stream subscription
      StreamSubscription? progressSubscription;
      if (onProgress != null) {
        progressSubscription = uploadTask.snapshotEvents.listen((taskSnapshot) {
          if (taskSnapshot.totalBytes > 0) {
            final progress = taskSnapshot.bytesTransferred / taskSnapshot.totalBytes;
            onProgress(progress.clamp(0.0, 1.0));
          }
        }, onError: (error) {
          // Progress stream error
          print('Upload progress error: $error');
        });
      }

      try {
        // Wait for upload to complete
        final taskSnapshot = await uploadTask;
        
        // Cancel progress subscription
        await progressSubscription?.cancel();
        
        // Check if upload was successful
        if (taskSnapshot.state == TaskState.success) {
          // Get download URL
          final downloadUrl = await taskSnapshot.ref.getDownloadURL();
          return downloadUrl;
        } else {
          throw Exception('Upload failed with state: ${taskSnapshot.state}');
        }
      } catch (uploadError) {
        await progressSubscription?.cancel();
        rethrow;
      }
    } on FirebaseException catch (e) {
      throw Exception('Firebase Storage error: ${e.code} - ${e.message}');
    } catch (e) {
      throw Exception('Firebase Storage upload failed: ${e.toString()}');
    }
  }

  Future<String> _uploadToPhpBackend(
    PrintFile printFile,
    String userId,
    String orderId,
    Function(double)? onProgress,
  ) async {
    try {
      final fileName = printFile.name;
      final request = http.MultipartRequest(
        'POST',
        Uri.parse(ApiConfig.uploadFileUrl),
      );

      // Handle both web (bytes) and mobile/desktop (path)
      if (kIsWeb && printFile.bytes != null) {
        // On web, use bytes
        request.files.add(
          http.MultipartFile.fromBytes(
            'file',
            printFile.bytes!,
            filename: fileName,
          ),
        );
      } else if (!kIsWeb && printFile.path != null) {
        // On mobile/desktop, use path
        request.files.add(
          await http.MultipartFile.fromPath('file', printFile.path!),
        );
      } else {
        throw Exception('File data not available for upload');
      }

      // Add form fields
      request.fields['user_id'] = userId;
      if (orderId.isNotEmpty) {
        request.fields['order_id'] = orderId;
      }

      // Update progress to show upload starting
      if (onProgress != null) {
        onProgress(0.5); // Show 50% when starting upload
      }

      // Send request and get response
      final streamedResponse = await request.send();
      
      // Update progress to show upload in progress
      if (onProgress != null) {
        onProgress(0.8); // Show 80% when request is sent
      }

      // Read response
      final response = await http.Response.fromStream(streamedResponse);
      
      // Update progress to complete after response is received
      if (onProgress != null) {
        onProgress(1.0);
      }

      // Check if response is HTML (PHP error) instead of JSON
      final responseBody = response.body.trim();
      if (responseBody.startsWith('<') || responseBody.contains('<html') || responseBody.contains('<br')) {
        // Extract error message from HTML if possible
        String errorMsg = 'Server returned HTML instead of JSON. ';
        if (responseBody.contains('Fatal error') || responseBody.contains('Warning') || responseBody.contains('Error')) {
          // Try to extract PHP error message
          final errorMatch = RegExp(r'(Fatal error|Warning|Parse error|Notice):\s*(.+?)(?:<|$)').firstMatch(responseBody);
          if (errorMatch != null) {
            errorMsg += errorMatch.group(2) ?? 'Unknown PHP error';
          } else {
            errorMsg += 'Check PHP error logs or XAMPP error logs.';
          }
        } else {
          errorMsg += 'This usually means there\'s a PHP error. Check that the database exists and XAMPP is running.';
        }
        throw Exception(errorMsg);
      }

      if (response.statusCode == 200) {
        try {
          final jsonData = json.decode(responseBody);
          if (jsonData['success'] == true) {
            // Return the file URL from the response
            return jsonData['file']['file_url'] as String;
          } else {
            throw Exception(jsonData['error'] ?? 'Upload failed');
          }
        } catch (e) {
          if (e is FormatException) {
            final preview = responseBody.length > 200 ? responseBody.substring(0, 200) + '...' : responseBody;
            throw Exception('Invalid JSON response from server. Response: $preview');
          }
          rethrow;
        }
      } else {
        try {
          final errorData = json.decode(responseBody);
          throw Exception(errorData['error'] ?? 'Upload failed: ${response.statusCode}');
        } catch (e) {
          if (e is FormatException) {
            final preview = responseBody.length > 200 ? responseBody.substring(0, 200) + '...' : responseBody;
            throw Exception('Upload failed: ${response.statusCode}. Server response: $preview');
          }
          final preview = responseBody.length > 200 ? responseBody.substring(0, 200) + '...' : responseBody;
          throw Exception('Upload failed: ${response.statusCode} - $preview');
        }
      }
    } catch (e) {
      // Provide more helpful error messages
      if (e.toString().contains('Failed to fetch') || 
          e.toString().contains('ClientException')) {
        throw Exception(
          'Cannot connect to backend server. '
          'Please make sure XAMPP is running and accessible at ${ApiConfig.uploadFileUrl}'
        );
      }
      rethrow;
    }
  }

  String _getContentType(String fileType) {
    final ext = fileType.toLowerCase();
    switch (ext) {
      case 'pdf':
        return 'application/pdf';
      case 'jpg':
      case 'jpeg':
        return 'image/jpeg';
      case 'png':
        return 'image/png';
      case 'doc':
        return 'application/msword';
      case 'docx':
        return 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';
      case 'ppt':
        return 'application/vnd.ms-powerpoint';
      case 'pptx':
        return 'application/vnd.openxmlformats-officedocument.presentationml.presentation';
      case 'xls':
        return 'application/vnd.ms-excel';
      case 'xlsx':
        return 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
      default:
        return 'application/octet-stream';
    }
  }

  Future<List<String>> uploadFiles(
    List<PrintFile> files,
    String userId,
    String orderId,
    Function(int current, int total, double progress)? onProgress,
  ) async {
    List<String> urls = [];
    int total = files.length;
    
    for (int i = 0; i < files.length; i++) {
      final url = await uploadFile(files[i], userId, orderId, (progress) {
        if (onProgress != null) {
          final overallProgress = (i + progress) / total;
          onProgress(i + 1, total, overallProgress);
        }
      });
      urls.add(url);
    }
    
    return urls;
  }

  Future<void> deleteFile(String urlOrId) async {
    try {
      // Extract file ID from URL or use it directly
      String fileId = urlOrId;
      if (urlOrId.contains('/')) {
        // Extract ID from URL path
        final parts = urlOrId.split('/');
        fileId = parts.last.split('_').first.replaceAll('file-', '');
      }

      final response = await http.post(
        Uri.parse(ApiConfig.deleteFileUrl),
        headers: {'Content-Type': 'application/json'},
        body: json.encode({'id': fileId}),
      );

      if (response.statusCode != 200) {
        throw Exception('Failed to delete file');
      }
    } catch (e) {
      // Ignore deletion errors
    }
  }

  int estimateTotalPages(List<PrintFile> files) {
    int total = 0;
    for (var file in files) {
      total += file.pageCount ?? _estimatePages(file);
    }
    return total;
  }

  int _estimatePages(PrintFile file) {
    if (file.isPdf) {
      return ((file.sizeBytes / 1024) / 50).ceil().clamp(1, 1000);
    } else if (file.isImage) {
      return 1;
    } else {
      return ((file.sizeBytes / 1024) / 30).ceil().clamp(1, 1000);
    }
  }
}

