import 'dart:io';
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../providers/cart_provider.dart';
import '../services/file_upload_service.dart';
import '../models/print_file.dart';
import '../utils/constants.dart';
import '../config/app_config.dart';
import 'configure_screen.dart';

class UploadScreen extends StatefulWidget {
  const UploadScreen({super.key});

  @override
  State<UploadScreen> createState() => _UploadScreenState();
}

class _UploadScreenState extends State<UploadScreen> {
  final FileUploadService _fileUploadService = FileUploadService();

  Future<void> _pickFiles() async {
    try {
      final files = await _fileUploadService.pickFiles();
      if (files.isNotEmpty) {
        final cartProvider = Provider.of<CartProvider>(context, listen: false);
        cartProvider.addFiles(files);
      }
    } catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(e.toString()),
          backgroundColor: AppConstants.errorColor,
        ),
      );
    }
  }

  void _removeFile(String fileId) {
    final cartProvider = Provider.of<CartProvider>(context, listen: false);
    cartProvider.removeFile(fileId);
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Upload Documents'),
      ),
      body: Consumer<CartProvider>(
        builder: (context, cartProvider, _) {
          return Column(
            children: [
              Expanded(
                child: cartProvider.files.isEmpty
                    ? _buildEmptyState()
                    : _buildFileList(cartProvider.files),
              ),
              if (cartProvider.files.isNotEmpty)
                Container(
                  padding: const EdgeInsets.all(AppConstants.paddingMedium),
                  decoration: BoxDecoration(
                    color: Theme.of(context).colorScheme.surface,
                    boxShadow: [
                      BoxShadow(
                        color: Colors.black.withOpacity(0.1),
                        blurRadius: 4,
                        offset: const Offset(0, -2),
                      ),
                    ],
                  ),
                  child: SafeArea(
                    child: Column(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: [
                            Text(
                              '${cartProvider.files.length} file(s) selected',
                              style: Theme.of(context).textTheme.titleMedium,
                            ),
                            TextButton.icon(
                              onPressed: _pickFiles,
                              icon: const Icon(Icons.add),
                              label: const Text('Add More'),
                            ),
                          ],
                        ),
                        const SizedBox(height: 8),
                        SizedBox(
                          width: double.infinity,
                          child: ElevatedButton(
                            onPressed: () {
                              Navigator.push(
                                context,
                                MaterialPageRoute(
                                  builder: (_) => const ConfigureScreen(),
                                ),
                              );
                            },
                            style: ElevatedButton.styleFrom(
                              padding: const EdgeInsets.symmetric(vertical: 16),
                            ),
                            child: const Text('Continue to Configuration'),
                          ),
                        ),
                      ],
                    ),
                  ),
                ),
            ],
          );
        },
      ),
      floatingActionButton: Consumer<CartProvider>(
        builder: (context, cartProvider, _) {
          if (cartProvider.files.isNotEmpty) return const SizedBox.shrink();
          return FloatingActionButton.extended(
            onPressed: _pickFiles,
            icon: const Icon(Icons.upload_file),
            label: const Text('Select Files'),
          );
        },
      ),
    );
  }

  Widget _buildEmptyState() {
    return Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Icon(
            Icons.cloud_upload_outlined,
            size: 100,
            color: Colors.grey[400],
          ),
          const SizedBox(height: 24),
          Text(
            'No files selected',
            style: Theme.of(context).textTheme.titleLarge?.copyWith(
                  color: Colors.grey[600],
                ),
          ),
          const SizedBox(height: 8),
          Text(
            'Tap the button below to select files',
            style: Theme.of(context).textTheme.bodyMedium?.copyWith(
                  color: Colors.grey,
                ),
          ),
          const SizedBox(height: 24),
          ElevatedButton.icon(
            onPressed: _pickFiles,
            icon: const Icon(Icons.upload_file),
            label: const Text('Select Files'),
            style: ElevatedButton.styleFrom(
              padding: const EdgeInsets.symmetric(
                horizontal: 24,
                vertical: 12,
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildFileList(List<PrintFile> files) {
    return ListView.builder(
      padding: const EdgeInsets.all(AppConstants.paddingMedium),
      itemCount: files.length,
      itemBuilder: (context, index) {
        final file = files[index];
        return Card(
          margin: const EdgeInsets.only(bottom: 12),
          child: ListTile(
            leading: _getFileIcon(file.fileType),
            title: Text(
              file.name,
              overflow: TextOverflow.ellipsis,
            ),
            subtitle: Text('${file.sizeInMB} MB'),
            trailing: IconButton(
              icon: const Icon(Icons.close, color: AppConstants.errorColor),
              onPressed: () => _removeFile(file.id),
            ),
          ),
        );
      },
    );
  }

  Widget _getFileIcon(String fileType) {
    IconData icon;
    Color color;

    switch (fileType.toLowerCase()) {
      case 'pdf':
        icon = Icons.picture_as_pdf;
        color = Colors.red;
        break;
      case 'doc':
      case 'docx':
        icon = Icons.description;
        color = Colors.blue;
        break;
      case 'jpg':
      case 'jpeg':
      case 'png':
        icon = Icons.image;
        color = Colors.green;
        break;
      case 'ppt':
      case 'pptx':
        icon = Icons.slideshow;
        color = Colors.orange;
        break;
      case 'xls':
      case 'xlsx':
        icon = Icons.table_chart;
        color = Colors.green;
        break;
      default:
        icon = Icons.insert_drive_file;
        color = Colors.grey;
    }

    return CircleAvatar(
      backgroundColor: color.withOpacity(0.1),
      child: Icon(icon, color: color),
    );
  }
}

