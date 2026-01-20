import 'package:cloud_firestore/cloud_firestore.dart';
import 'package:qr_flutter/qr_flutter.dart';
import '../models/print_order.dart';
import '../models/print_file.dart';
import '../models/print_option.dart';
import '../models/user_model.dart' show Address;
import '../utils/order_id_generator.dart';

class OrderService {
  final FirebaseFirestore _firestore = FirebaseFirestore.instance;

  Future<PrintOrder> createOrder({
    required String userId,
    required List<PrintFile> files,
    required PrintOption printOption,
    required double totalCost,
    required String deliveryOption,
    Address? deliveryAddress,
  }) async {
    try {
      final orderId = OrderIdGenerator.generateOrderId();
      final qrCode = orderId; // Use order ID as QR code data

      // Update files with order ID
      final updatedFiles = files.map((file) {
        return PrintFile(
          id: file.id,
          name: file.name,
          path: file.path,
          bytes: file.bytes,
          firebaseStorageUrl: file.firebaseStorageUrl,
          sizeBytes: file.sizeBytes,
          fileType: file.fileType,
          pageCount: file.pageCount,
          uploadedAt: file.uploadedAt,
        );
      }).toList();

      final estimatedReady = DateTime.now().add(const Duration(hours: 24));

      final order = PrintOrder(
        orderId: orderId,
        userId: userId,
        files: updatedFiles,
        printOption: printOption,
        totalCost: totalCost,
        status: 'Pending',
        deliveryOption: deliveryOption,
        deliveryAddress: deliveryAddress,
        estimatedReady: estimatedReady,
        qrCode: qrCode,
      );

      await _firestore
          .collection('orders')
          .doc(orderId)
          .set(order.toMap());

      return order;
    } catch (e) {
      rethrow;
    }
  }

  Future<void> updateOrderStatus(String orderId, String status) async {
    try {
      final updateData = <String, dynamic>{'status': status};
      
      if (status == 'Completed') {
        updateData['completedAt'] = DateTime.now().toIso8601String();
      }

      await _firestore.collection('orders').doc(orderId).update(updateData);
    } catch (e) {
      rethrow;
    }
  }

  Future<PrintOrder?> getOrder(String orderId) async {
    try {
      final doc = await _firestore.collection('orders').doc(orderId).get();
      if (doc.exists && doc.data() != null) {
        return PrintOrder.fromMap(doc.data()!);
      }
      return null;
    } catch (e) {
      return null;
    }
  }

  Stream<List<PrintOrder>> getUserOrders(String userId) {
    return _firestore
        .collection('orders')
        .where('userId', isEqualTo: userId)
        .orderBy('createdAt', descending: true)
        .snapshots()
        .map((snapshot) {
      return snapshot.docs
          .map((doc) => PrintOrder.fromMap(doc.data()))
          .toList();
    });
  }

  Stream<PrintOrder?> watchOrder(String orderId) {
    return _firestore
        .collection('orders')
        .doc(orderId)
        .snapshots()
        .map((doc) {
      if (doc.exists && doc.data() != null) {
        return PrintOrder.fromMap(doc.data()!);
      }
      return null;
    });
  }

  Future<List<PrintOrder>> getOrdersByStatus(
      String userId, String status) async {
    try {
      final querySnapshot = await _firestore
          .collection('orders')
          .where('userId', isEqualTo: userId)
          .where('status', isEqualTo: status)
          .orderBy('createdAt', descending: true)
          .get();

      return querySnapshot.docs
          .map((doc) => PrintOrder.fromMap(doc.data()))
          .toList();
    } catch (e) {
      return [];
    }
  }

  Future<void> deleteOrder(String orderId) async {
    try {
      await _firestore.collection('orders').doc(orderId).delete();
    } catch (e) {
      rethrow;
    }
  }
}

