import 'package:flutter/foundation.dart';
import '../models/print_file.dart';
import '../models/print_option.dart';

class CartProvider with ChangeNotifier {
  List<PrintFile> _files = [];
  PrintOption _printOption = PrintOption();
  bool _isUploading = false;
  double _uploadProgress = 0.0;

  List<PrintFile> get files => _files;
  PrintOption get printOption => _printOption;
  bool get isUploading => _isUploading;
  double get uploadProgress => _uploadProgress;

  void addFiles(List<PrintFile> newFiles) {
    _files.addAll(newFiles);
    notifyListeners();
  }

  void removeFile(String fileId) {
    _files.removeWhere((file) => file.id == fileId);
    notifyListeners();
  }

  void clearFiles() {
    _files.clear();
    notifyListeners();
  }

  void updatePrintOption(PrintOption option) {
    _printOption = option;
    notifyListeners();
  }

  void setUploading(bool uploading) {
    _isUploading = uploading;
    if (!uploading) {
      _uploadProgress = 0.0;
    }
    notifyListeners();
  }

  void setUploadProgress(double progress) {
    _uploadProgress = progress;
    notifyListeners();
  }

  void clearCart() {
    _files.clear();
    _printOption = PrintOption();
    _isUploading = false;
    _uploadProgress = 0.0;
    notifyListeners();
  }
}

