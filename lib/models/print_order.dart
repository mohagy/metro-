import 'print_file.dart';
import 'print_option.dart';
import 'user_model.dart' show Address;
import '../utils/order_id_generator.dart';

class PrintOrder {
  final String orderId;
  final String userId;
  final List<PrintFile> files;
  final PrintOption printOption;
  final double totalCost;
  final String status;
  final String deliveryOption;
  final Address? deliveryAddress;
  final DateTime createdAt;
  final DateTime? estimatedReady;
  final DateTime? completedAt;
  final String? qrCode;

  PrintOrder({
    String? orderId,
    required this.userId,
    required this.files,
    required this.printOption,
    required this.totalCost,
    required this.status,
    required this.deliveryOption,
    this.deliveryAddress,
    DateTime? createdAt,
    this.estimatedReady,
    this.completedAt,
    this.qrCode,
  })  : orderId = orderId ?? OrderIdGenerator.generateOrderId(),
        createdAt = createdAt ?? DateTime.now();

  Map<String, dynamic> toMap() {
    return {
      'orderId': orderId,
      'userId': userId,
      'files': files.map((f) => f.toMap()).toList(),
      'printOption': printOption.toMap(),
      'totalCost': totalCost,
      'status': status,
      'deliveryOption': deliveryOption,
      'deliveryAddress': deliveryAddress?.toMap(),
      'createdAt': createdAt.toIso8601String(),
      'estimatedReady': estimatedReady?.toIso8601String(),
      'completedAt': completedAt?.toIso8601String(),
      'qrCode': qrCode,
    };
  }

  factory PrintOrder.fromMap(Map<String, dynamic> map) {
    return PrintOrder(
      orderId: map['orderId'] ?? '',
      userId: map['userId'] ?? '',
      files: (map['files'] as List<dynamic>?)
              ?.map((f) => PrintFile.fromMap(f as Map<String, dynamic>))
              .toList() ??
          [],
      printOption: PrintOption.fromMap(
          map['printOption'] as Map<String, dynamic>),
      totalCost: (map['totalCost'] ?? 0.0).toDouble(),
      status: map['status'] ?? 'Pending',
      deliveryOption: map['deliveryOption'] ?? 'Pickup',
      deliveryAddress: map['deliveryAddress'] != null
          ? Address.fromMap(map['deliveryAddress'] as Map<String, dynamic>)
          : null,
      createdAt: map['createdAt'] != null
          ? DateTime.parse(map['createdAt'])
          : DateTime.now(),
      estimatedReady: map['estimatedReady'] != null
          ? DateTime.parse(map['estimatedReady'])
          : null,
      completedAt: map['completedAt'] != null
          ? DateTime.parse(map['completedAt'])
          : null,
      qrCode: map['qrCode'],
    );
  }

  PrintOrder copyWith({
    String? orderId,
    String? userId,
    List<PrintFile>? files,
    PrintOption? printOption,
    double? totalCost,
    String? status,
    String? deliveryOption,
    Address? deliveryAddress,
    DateTime? createdAt,
    DateTime? estimatedReady,
    DateTime? completedAt,
    String? qrCode,
  }) {
    return PrintOrder(
      orderId: orderId ?? this.orderId,
      userId: userId ?? this.userId,
      files: files ?? this.files,
      printOption: printOption ?? this.printOption,
      totalCost: totalCost ?? this.totalCost,
      status: status ?? this.status,
      deliveryOption: deliveryOption ?? this.deliveryOption,
      deliveryAddress: deliveryAddress ?? this.deliveryAddress,
      createdAt: createdAt ?? this.createdAt,
      estimatedReady: estimatedReady ?? this.estimatedReady,
      completedAt: completedAt ?? this.completedAt,
      qrCode: qrCode ?? this.qrCode,
    );
  }

  String get referenceNumber {
    return OrderIdGenerator.getReferenceNumber(orderId);
  }

  bool get isPending => status == 'Pending';
  bool get isPrinting => status == 'Printing';
  bool get isReady => status == 'Ready';
  bool get isCompleted => status == 'Completed';
}

