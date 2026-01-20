import 'dart:math';

class OrderIdGenerator {
  static String generateOrderId() {
    final now = DateTime.now();
    final datePart = 
        '${now.year}${now.month.toString().padLeft(2, '0')}${now.day.toString().padLeft(2, '0')}';
    final timePart = 
        '${now.hour.toString().padLeft(2, '0')}${now.minute.toString().padLeft(2, '0')}${now.second.toString().padLeft(2, '0')}';
    final randomPart = Random().nextInt(999).toString().padLeft(3, '0');
    return 'PRT-$datePart-$timePart-$randomPart';
  }
  
  static String getReferenceNumber(String orderId) {
    final parts = orderId.split('-');
    if (parts.length >= 3) {
      return parts.last; // Return last 3 digits
    }
    return orderId.substring(orderId.length - 6);
  }
}

