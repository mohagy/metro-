import 'dart:io';
import 'package:flutter/material.dart';
import '../models/print_file.dart';
import '../utils/constants.dart';

class FileUploadWidget extends StatelessWidget {
  final PrintFile file;
  final VoidCallback? onRemove;
  final VoidCallback? onTap;

  const FileUploadWidget({
    super.key,
    required this.file,
    this.onRemove,
    this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return Card(
      margin: const EdgeInsets.only(bottom: 12),
      child: ListTile(
        leading: _getFileIcon(file.fileType),
        title: Text(
          file.name,
          overflow: TextOverflow.ellipsis,
        ),
        subtitle: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text('${file.sizeInMB} MB'),
            if (file.pageCount != null)
              Text('${file.pageCount} page(s)'),
          ],
        ),
        trailing: onRemove != null
            ? IconButton(
                icon: const Icon(Icons.close, color: AppConstants.errorColor),
                onPressed: onRemove,
              )
            : null,
        onTap: onTap,
      ),
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

