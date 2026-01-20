import 'package:flutter/material.dart';
import '../config/app_config.dart';
import '../utils/constants.dart';

class StatusIndicator extends StatelessWidget {
  final String status;

  const StatusIndicator({
    super.key,
    required this.status,
  });

  @override
  Widget build(BuildContext context) {
    Color color;
    IconData icon;

    switch (status) {
      case AppConfig.statusPending:
        color = Colors.orange;
        icon = Icons.pending;
        break;
      case AppConfig.statusPrinting:
        color = Colors.blue;
        icon = Icons.print;
        break;
      case AppConfig.statusReady:
        color = Colors.green;
        icon = Icons.check_circle;
        break;
      case AppConfig.statusCompleted:
        color = AppConstants.successColor;
        icon = Icons.done_all;
        break;
      case AppConfig.statusCancelled:
        color = AppConstants.errorColor;
        icon = Icons.cancel;
        break;
      default:
        color = Colors.grey;
        icon = Icons.help;
    }

    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
      decoration: BoxDecoration(
        color: color.withOpacity(0.1),
        borderRadius: BorderRadius.circular(AppConstants.borderRadiusSmall),
        border: Border.all(color: color.withOpacity(0.3)),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(icon, size: 16, color: color),
          const SizedBox(width: 6),
          Text(
            status,
            style: TextStyle(
              color: color,
              fontWeight: FontWeight.w600,
              fontSize: 12,
            ),
          ),
        ],
      ),
    );
  }
}

