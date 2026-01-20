import 'dart:typed_data';

class PrintFile {
  final String id;
  final String name;
  final String? path; // null on web
  final Uint8List? bytes; // used on web instead of path
  final String? firebaseStorageUrl;
  final int sizeBytes;
  final String fileType;
  final int? pageCount;
  final DateTime uploadedAt;

  PrintFile({
    required this.id,
    required this.name,
    this.path,
    this.bytes,
    this.firebaseStorageUrl,
    required this.sizeBytes,
    required this.fileType,
    this.pageCount,
    DateTime? uploadedAt,
  }) : uploadedAt = uploadedAt ?? DateTime.now(),
       assert(
         firebaseStorageUrl != null || path != null || bytes != null,
         'Either firebaseStorageUrl, path, or bytes must be provided'
       );

  Map<String, dynamic> toMap() {
    return {
      'id': id,
      'name': name,
      'path': path,
      'firebaseStorageUrl': firebaseStorageUrl,
      'sizeBytes': sizeBytes,
      'fileType': fileType,
      'pageCount': pageCount,
      'uploadedAt': uploadedAt.toIso8601String(),
      // Note: bytes are not serialized to avoid large JSON payloads
    };
  }

  factory PrintFile.fromMap(Map<String, dynamic> map) {
    // When deserializing from storage, files already have firebaseStorageUrl
    // so path/bytes are not needed (they're only needed during upload)
    return PrintFile(
      id: map['id'] ?? '',
      name: map['name'] ?? '',
      path: map['path'], // Can be null for web-uploaded files
      bytes: null, // Not stored in map
      firebaseStorageUrl: map['firebaseStorageUrl'],
      sizeBytes: map['sizeBytes'] ?? 0,
      fileType: map['fileType'] ?? '',
      pageCount: map['pageCount'],
      uploadedAt: map['uploadedAt'] != null
          ? DateTime.parse(map['uploadedAt'])
          : DateTime.now(),
    );
  }

  String get sizeInMB {
    return (sizeBytes / (1024 * 1024)).toStringAsFixed(2);
  }

  bool get isImage {
    return ['jpg', 'jpeg', 'png', 'gif'].contains(fileType.toLowerCase());
  }

  bool get isPdf {
    return fileType.toLowerCase() == 'pdf';
  }

  PrintFile copyWith({
    String? id,
    String? name,
    String? path,
    Uint8List? bytes,
    String? firebaseStorageUrl,
    int? sizeBytes,
    String? fileType,
    int? pageCount,
    DateTime? uploadedAt,
  }) {
    return PrintFile(
      id: id ?? this.id,
      name: name ?? this.name,
      path: path ?? this.path,
      bytes: bytes ?? this.bytes,
      firebaseStorageUrl: firebaseStorageUrl ?? this.firebaseStorageUrl,
      sizeBytes: sizeBytes ?? this.sizeBytes,
      fileType: fileType ?? this.fileType,
      pageCount: pageCount ?? this.pageCount,
      uploadedAt: uploadedAt ?? this.uploadedAt,
    );
  }
}

