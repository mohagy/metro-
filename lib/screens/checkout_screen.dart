import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../providers/cart_provider.dart';
import '../providers/order_provider.dart';
import '../providers/auth_provider.dart';
import '../services/file_upload_service.dart';
import '../services/pricing_calculator.dart';
import '../models/user_model.dart' show Address;
import '../utils/constants.dart';
import '../utils/currency_formatter.dart';
import '../config/app_config.dart';
import '../models/print_option.dart' show BindingType;
import '../widgets/order_card.dart';
import 'tracking_screen.dart';

class CheckoutScreen extends StatefulWidget {
  const CheckoutScreen({super.key});

  @override
  State<CheckoutScreen> createState() => _CheckoutScreenState();
}

class _CheckoutScreenState extends State<CheckoutScreen> {
  final FileUploadService _fileUploadService = FileUploadService();
  String _selectedDeliveryOption = AppConfig.deliveryPickup;
  Address? _selectedAddress;
  bool _isSubmitting = false;

  Future<void> _submitOrder() async {
    if (_selectedDeliveryOption != AppConfig.deliveryPickup &&
        _selectedAddress == null) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Please select a delivery address'),
          backgroundColor: AppConstants.errorColor,
        ),
      );
      return;
    }

    setState(() {
      _isSubmitting = true;
    });

    try {
      final authProvider = Provider.of<AuthProvider>(context, listen: false);
      final cartProvider = Provider.of<CartProvider>(context, listen: false);
      final orderProvider = Provider.of<OrderProvider>(context, listen: false);

      if (authProvider.user == null) {
        throw Exception('User not authenticated');
      }

      // Upload files to Firebase Storage
      final userId = authProvider.user!.uid;
      final tempOrderId = 'temp-${DateTime.now().millisecondsSinceEpoch}';

      cartProvider.setUploading(true);
      final uploadedUrls = await _fileUploadService.uploadFiles(
        cartProvider.files,
        userId,
        tempOrderId,
        (current, total, progress) {
          cartProvider.setUploadProgress(progress);
        },
      );

      // Update files with storage URLs
      final updatedFiles = cartProvider.files.asMap().entries.map((entry) {
        final file = entry.value;
        return file.copyWith(
          firebaseStorageUrl: uploadedUrls[entry.key],
        );
      }).toList();

      // Calculate total cost
      final totalCost = PricingCalculator.calculateCost(
        files: updatedFiles,
        printOption: cartProvider.printOption,
      );

      // Create order
      final order = await orderProvider.createOrder(
        userId: userId,
        files: updatedFiles,
        printOption: cartProvider.printOption,
        totalCost: totalCost,
        deliveryOption: _selectedDeliveryOption,
        deliveryAddress: _selectedAddress,
      );

      cartProvider.setUploading(false);
      cartProvider.clearCart();

      if (!mounted) return;

      if (order != null) {
        Navigator.pushReplacement(
          context,
          MaterialPageRoute(
            builder: (_) => TrackingScreen(orderId: order.orderId),
          ),
        );
      } else {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(orderProvider.error ?? 'Failed to create order'),
            backgroundColor: AppConstants.errorColor,
          ),
        );
      }
    } catch (e) {
      setState(() {
        _isSubmitting = false;
      });
      final cartProvider = Provider.of<CartProvider>(context, listen: false);
      cartProvider.setUploading(false);

      if (!mounted) return;

      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text('Error: ${e.toString()}'),
          backgroundColor: AppConstants.errorColor,
        ),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Checkout'),
      ),
      body: Consumer2<CartProvider, AuthProvider>(
        builder: (context, cartProvider, authProvider, _) {
          if (cartProvider.files.isEmpty) {
            return const Center(
              child: Text('No files in cart. Please go back and add files.'),
            );
          }

          final totalCost = PricingCalculator.calculateCost(
            files: cartProvider.files,
            printOption: cartProvider.printOption,
          );

          return Column(
            children: [
              Expanded(
                child: SingleChildScrollView(
                  padding: const EdgeInsets.all(AppConstants.paddingMedium),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.stretch,
                    children: [
                      // Order Summary
                      _buildSectionTitle('Order Summary'),
                      Card(
                        child: Padding(
                          padding: const EdgeInsets.all(AppConstants.paddingMedium),
                          child: Column(
                            children: [
                              ...cartProvider.files.map((file) => ListTile(
                                    leading: const Icon(Icons.description),
                                    title: Text(file.name),
                                    subtitle: Text('${file.sizeInMB} MB'),
                                  )),
                            ],
                          ),
                        ),
                      ),
                      const SizedBox(height: 24),

                      // Print Options Summary
                      _buildSectionTitle('Print Options'),
                      Card(
                        child: Padding(
                          padding: const EdgeInsets.all(AppConstants.paddingMedium),
                          child: Column(
                            children: [
                              _buildSummaryRow(
                                  'Paper Size', cartProvider.printOption.paperSizeLabel),
                              _buildSummaryRow(
                                  'Color', cartProvider.printOption.colorLabel),
                              _buildSummaryRow(
                                  'Quantity', '${cartProvider.printOption.quantity}'),
                              _buildSummaryRow(
                                  'Sides', cartProvider.printOption.sidesLabel),
                              _buildSummaryRow('Orientation',
                                  cartProvider.printOption.orientationLabel),
                              if (cartProvider.printOption.binding !=
                                  BindingType.none)
                                _buildSummaryRow('Binding',
                                    cartProvider.printOption.bindingLabel),
                            ],
                          ),
                        ),
                      ),
                      const SizedBox(height: 24),

                      // Cost Breakdown
                      _buildSectionTitle('Cost Breakdown'),
                      Card(
                        child: Padding(
                          padding: const EdgeInsets.all(AppConstants.paddingMedium),
                          child: Column(
                            children: [
                              ...PricingCalculator.getCostBreakdown(
                                files: cartProvider.files,
                                printOption: cartProvider.printOption,
                              ).entries.map((entry) {
                                if (entry.key == 'total' || entry.key == 'pages') {
                                  return const SizedBox.shrink();
                                }
                                return _buildSummaryRow(
                                  entry.key == 'paperCost'
                                      ? 'Paper Cost'
                                      : entry.key == 'bindingCost'
                                          ? 'Binding'
                                          : 'Service Fee',
                                  CurrencyFormatter.format(entry.value),
                                  isSubtotal: true,
                                );
                              }),
                              const Divider(),
                              _buildSummaryRow(
                                'Total',
                                CurrencyFormatter.format(totalCost),
                                isTotal: true,
                              ),
                            ],
                          ),
                        ),
                      ),
                      const SizedBox(height: 24),

                      // Delivery Option
                      _buildSectionTitle('Delivery Option'),
                      Card(
                        child: Column(
                          children: [
                            RadioListTile<String>(
                              title: const Text('Pickup'),
                              value: AppConfig.deliveryPickup,
                              groupValue: _selectedDeliveryOption,
                              onChanged: (value) {
                                setState(() {
                                  _selectedDeliveryOption = value!;
                                  _selectedAddress = null;
                                });
                              },
                            ),
                            RadioListTile<String>(
                              title: const Text('Home Delivery'),
                              value: AppConfig.deliveryHome,
                              groupValue: _selectedDeliveryOption,
                              onChanged: (value) {
                                setState(() {
                                  _selectedDeliveryOption = value!;
                                });
                              },
                            ),
                            RadioListTile<String>(
                              title: const Text('Office Delivery'),
                              value: AppConfig.deliveryOffice,
                              groupValue: _selectedDeliveryOption,
                              onChanged: (value) {
                                setState(() {
                                  _selectedDeliveryOption = value!;
                                });
                              },
                            ),
                          ],
                        ),
                      ),
                      const SizedBox(height: 24),

                      // Address Selection (if delivery)
                      if (_selectedDeliveryOption != AppConfig.deliveryPickup) ...[
                        _buildSectionTitle('Delivery Address'),
                        _buildAddressSelector(authProvider),
                        const SizedBox(height: 24),
                      ],
                    ],
                  ),
                ),
              ),
              
              // Submit Button
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
                      if (cartProvider.isUploading)
                        Column(
                          children: [
                            LinearProgressIndicator(
                              value: cartProvider.uploadProgress,
                            ),
                            const SizedBox(height: 8),
                            Text(
                              'Uploading files... ${(cartProvider.uploadProgress * 100).toStringAsFixed(0)}%',
                              style: Theme.of(context).textTheme.bodySmall,
                            ),
                            const SizedBox(height: 16),
                          ],
                        ),
                      SizedBox(
                        width: double.infinity,
                        child: ElevatedButton(
                          onPressed: (_isSubmitting || cartProvider.isUploading)
                              ? null
                              : _submitOrder,
                          style: ElevatedButton.styleFrom(
                            padding: const EdgeInsets.symmetric(vertical: 16),
                          ),
                          child: _isSubmitting
                              ? const SizedBox(
                                  height: 20,
                                  width: 20,
                                  child: CircularProgressIndicator(strokeWidth: 2),
                                )
                              : const Text('Place Order'),
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
    );
  }

  Widget _buildSectionTitle(String title) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 8),
      child: Text(
        title,
        style: Theme.of(context).textTheme.titleMedium?.copyWith(
              fontWeight: FontWeight.bold,
            ),
      ),
    );
  }

  Widget _buildSummaryRow(String label, String value,
      {bool isSubtotal = false, bool isTotal = false}) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 4),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Text(
            label,
            style: isTotal
                ? Theme.of(context).textTheme.titleMedium?.copyWith(
                      fontWeight: FontWeight.bold,
                    )
                : null,
          ),
          Text(
            value,
            style: isTotal
                ? Theme.of(context).textTheme.titleMedium?.copyWith(
                      fontWeight: FontWeight.bold,
                      color: Theme.of(context).colorScheme.primary,
                    )
                : null,
          ),
        ],
      ),
    );
  }

  Widget _buildAddressSelector(AuthProvider authProvider) {
    final addresses = authProvider.userData?.addresses ?? [];

    if (addresses.isEmpty) {
      return Card(
        child: Padding(
          padding: const EdgeInsets.all(AppConstants.paddingMedium),
          child: Column(
            children: [
              const Text('No saved addresses. Please add an address in your profile.'),
              const SizedBox(height: 16),
              ElevatedButton(
                onPressed: () {
                  // Navigate to profile to add address
                  Navigator.pop(context);
                },
                child: const Text('Add Address'),
              ),
            ],
          ),
        ),
      );
    }

    return Card(
      child: Column(
        children: addresses.map((address) {
          final isSelected = _selectedAddress?.id == address.id;
          return RadioListTile<Address>(
            title: Text(address.label),
            subtitle: Text(address.fullAddress),
            value: address,
            groupValue: _selectedAddress,
            onChanged: (value) {
              setState(() {
                _selectedAddress = value;
              });
            },
            selected: isSelected,
          );
        }).toList(),
      ),
    );
  }
}


