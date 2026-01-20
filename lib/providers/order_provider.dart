import 'package:flutter/foundation.dart';
import '../models/print_order.dart';
import '../models/print_file.dart';
import '../models/print_option.dart';
import '../models/user_model.dart' show Address;
import '../services/order_service.dart';

class OrderProvider with ChangeNotifier {
  final OrderService _orderService = OrderService();
  List<PrintOrder> _orders = [];
  PrintOrder? _currentOrder;
  bool _isLoading = false;
  String? _error;

  List<PrintOrder> get orders => _orders;
  PrintOrder? get currentOrder => _currentOrder;
  bool get isLoading => _isLoading;
  String? get error => _error;

  List<PrintOrder> get pendingOrders =>
      _orders.where((o) => o.status == 'Pending').toList();
  List<PrintOrder> get activeOrders =>
      _orders.where((o) => ['Pending', 'Printing'].contains(o.status)).toList();
  List<PrintOrder> get completedOrders =>
      _orders.where((o) => o.status == 'Completed').toList();

  void loadUserOrders(String userId) {
    _orderService.getUserOrders(userId).listen((orders) {
      _orders = orders;
      notifyListeners();
    });
  }

  Future<PrintOrder?> createOrder({
    required String userId,
    required List<PrintFile> files,
    required PrintOption printOption,
    required double totalCost,
    required String deliveryOption,
    Address? deliveryAddress,
  }) async {
    try {
      _isLoading = true;
      _error = null;
      notifyListeners();

      final order = await _orderService.createOrder(
        userId: userId,
        files: files,
        printOption: printOption,
        totalCost: totalCost,
        deliveryOption: deliveryOption,
        deliveryAddress: deliveryAddress,
      );

      _currentOrder = order;
      _isLoading = false;
      notifyListeners();
      return order;
    } catch (e) {
      _isLoading = false;
      _error = e.toString();
      notifyListeners();
      return null;
    }
  }

  Future<void> loadOrder(String orderId) async {
    try {
      _isLoading = true;
      _error = null;
      notifyListeners();

      _currentOrder = await _orderService.getOrder(orderId);

      _isLoading = false;
      notifyListeners();
    } catch (e) {
      _isLoading = false;
      _error = e.toString();
      notifyListeners();
    }
  }

  void watchOrder(String orderId) {
    _orderService.watchOrder(orderId).listen((order) {
      _currentOrder = order;
      if (order != null) {
        // Update in orders list
        final index = _orders.indexWhere((o) => o.orderId == order.orderId);
        if (index >= 0) {
          _orders[index] = order;
        } else {
          _orders.insert(0, order);
        }
      }
      notifyListeners();
    });
  }

  Future<bool> updateOrderStatus(String orderId, String status) async {
    try {
      _isLoading = true;
      _error = null;
      notifyListeners();

      await _orderService.updateOrderStatus(orderId, status);

      _isLoading = false;
      notifyListeners();
      return true;
    } catch (e) {
      _isLoading = false;
      _error = e.toString();
      notifyListeners();
      return false;
    }
  }

  void clearCurrentOrder() {
    _currentOrder = null;
    notifyListeners();
  }

  void clearError() {
    _error = null;
    notifyListeners();
  }
}

