import 'package:firebase_messaging/firebase_messaging.dart';
import 'package:flutter/foundation.dart';

class NotificationService {
  final FirebaseMessaging _messaging = FirebaseMessaging.instance;

  Future<void> initialize() async {
    // Request permission
    NotificationSettings settings = await _messaging.requestPermission(
      alert: true,
      badge: true,
      sound: true,
    );

    if (settings.authorizationStatus == AuthorizationStatus.authorized) {
      // Listen for foreground messages
      FirebaseMessaging.onMessage.listen(_handleForegroundMessage);

      // Handle background messages
      FirebaseMessaging.onMessageOpenedApp.listen(_handleBackgroundMessage);
    }
  }

  void _handleForegroundMessage(RemoteMessage message) {
    // Handle foreground message
    // In a real app, you would show a local notification here
    // For now, we'll just log it
    debugPrint('Foreground message: ${message.notification?.title}');
  }

  void _handleBackgroundMessage(RemoteMessage message) {
    // Handle background message
  }

  Future<String?> getFCMToken() async {
    try {
      return await _messaging.getToken();
    } catch (e) {
      return null;
    }
  }

  Future<void> subscribeToOrderUpdates(String orderId) async {
    await _messaging.subscribeToTopic('order_$orderId');
  }

  Future<void> unsubscribeFromOrderUpdates(String orderId) async {
    await _messaging.unsubscribeFromTopic('order_$orderId');
  }

  Future<void> showLocalNotification({
    required String title,
    required String body,
    String? payload,
  }) async {
    // Show local notification
    // In a real app, you would use flutter_local_notifications here
    debugPrint('Notification: $title - $body');
  }
}

