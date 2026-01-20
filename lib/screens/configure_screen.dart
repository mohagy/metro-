import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../providers/cart_provider.dart';
import '../models/print_option.dart' as print_option;
import '../services/pricing_calculator.dart';
import '../utils/constants.dart';
import '../utils/currency_formatter.dart';
import '../config/app_config.dart';
import 'checkout_screen.dart';
import '../widgets/cost_calculator_widget.dart';

class ConfigureScreen extends StatefulWidget {
  const ConfigureScreen({super.key});

  @override
  State<ConfigureScreen> createState() => _ConfigureScreenState();
}

class _ConfigureScreenState extends State<ConfigureScreen> {
  late print_option.PrintOption _printOption;

  @override
  void initState() {
    super.initState();
    final cartProvider = Provider.of<CartProvider>(context, listen: false);
    _printOption = cartProvider.printOption;
  }

  void _updateOption(print_option.PrintOption option) {
    setState(() {
      _printOption = option;
    });
    final cartProvider = Provider.of<CartProvider>(context, listen: false);
    cartProvider.updatePrintOption(option);
  }

  double _calculateCost() {
    final cartProvider = Provider.of<CartProvider>(context, listen: false);
    return PricingCalculator.calculateCost(
      files: cartProvider.files,
      printOption: _printOption,
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Configure Print Options'),
      ),
      body: Consumer<CartProvider>(
        builder: (context, cartProvider, _) {
          if (cartProvider.files.isEmpty) {
            return const Center(
              child: Text('No files selected. Please go back and select files.'),
            );
          }

          return Column(
            children: [
              Expanded(
                child: SingleChildScrollView(
                  padding: const EdgeInsets.all(AppConstants.paddingMedium),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.stretch,
                    children: [
                      // Paper Size
                      _buildSectionTitle('Paper Size'),
                      _buildPaperSizeSelector(),
                      const SizedBox(height: 24),

                      // Color Option
                      _buildSectionTitle('Print Color'),
                      _buildColorSelector(),
                      const SizedBox(height: 24),

                      // Quantity
                      _buildSectionTitle('Quantity'),
                      _buildQuantitySelector(),
                      const SizedBox(height: 24),

                      // Print Sides
                      _buildSectionTitle('Print Sides'),
                      _buildSidesSelector(),
                      const SizedBox(height: 24),

                      // Orientation
                      _buildSectionTitle('Orientation'),
                      _buildOrientationSelector(),
                      const SizedBox(height: 24),

                      // Binding
                      _buildSectionTitle('Binding Options'),
                      _buildBindingSelector(),
                      const SizedBox(height: 24),

                      // Cost Preview
                      CostCalculatorWidget(
                        files: cartProvider.files,
                        printOption: _printOption,
                      ),
                    ],
                  ),
                ),
              ),
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
                            'Total Cost',
                            style: Theme.of(context).textTheme.titleLarge,
                          ),
                          Text(
                            CurrencyFormatter.format(_calculateCost()),
                            style: Theme.of(context)
                                .textTheme
                                .titleLarge
                                ?.copyWith(
                                  fontWeight: FontWeight.bold,
                                  color: Theme.of(context).colorScheme.primary,
                                ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 16),
                      SizedBox(
                        width: double.infinity,
                        child: ElevatedButton(
                          onPressed: () {
                            Navigator.push(
                              context,
                              MaterialPageRoute(
                                builder: (_) => const CheckoutScreen(),
                              ),
                            );
                          },
                          style: ElevatedButton.styleFrom(
                            padding: const EdgeInsets.symmetric(vertical: 16),
                          ),
                          child: const Text('Proceed to Checkout'),
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
    return Text(
      title,
      style: Theme.of(context).textTheme.titleMedium?.copyWith(
            fontWeight: FontWeight.bold,
          ),
    );
  }

  Widget _buildPaperSizeSelector() {
    return SegmentedButton<print_option.PaperSize>(
      segments: const [
        ButtonSegment(value: print_option.PaperSize.a4, label: Text('A4')),
        ButtonSegment(value: print_option.PaperSize.letter, label: Text('Letter')),
        ButtonSegment(value: print_option.PaperSize.legal, label: Text('Legal')),
        ButtonSegment(value: print_option.PaperSize.a3, label: Text('A3')),
      ],
      selected: {_printOption.paperSize},
      onSelectionChanged: (Set<print_option.PaperSize> selection) {
        _updateOption(_printOption.copyWith(paperSize: selection.first));
      },
    );
  }

  Widget _buildColorSelector() {
    return SegmentedButton<print_option.PrintColor>(
      segments: const [
        ButtonSegment(value: print_option.PrintColor.blackWhite, label: Text('B&W')),
        ButtonSegment(value: print_option.PrintColor.color, label: Text('Color')),
      ],
      selected: {_printOption.color},
      onSelectionChanged: (Set<print_option.PrintColor> selection) {
        _updateOption(_printOption.copyWith(color: selection.first));
      },
    );
  }

  Widget _buildQuantitySelector() {
    return Row(
      children: [
        IconButton(
          icon: const Icon(Icons.remove),
          onPressed: _printOption.quantity > 1
              ? () {
                  _updateOption(_printOption.copyWith(
                      quantity: _printOption.quantity - 1));
                }
              : null,
        ),
        Expanded(
          child: TextField(
            textAlign: TextAlign.center,
            keyboardType: TextInputType.number,
            controller: TextEditingController(
              text: _printOption.quantity.toString(),
            )..selection = TextSelection.fromPosition(
                TextPosition(offset: _printOption.quantity.toString().length)),
            onChanged: (value) {
              final quantity = int.tryParse(value) ?? 1;
              if (quantity > 0 && quantity <= 1000) {
                _updateOption(_printOption.copyWith(quantity: quantity));
              }
            },
            decoration: const InputDecoration(
              border: OutlineInputBorder(),
            ),
          ),
        ),
        IconButton(
          icon: const Icon(Icons.add),
          onPressed: _printOption.quantity < 1000
              ? () {
                  _updateOption(_printOption.copyWith(
                      quantity: _printOption.quantity + 1));
                }
              : null,
        ),
      ],
    );
  }

  Widget _buildSidesSelector() {
    return SegmentedButton<print_option.PrintSides>(
      segments: const [
        ButtonSegment(value: print_option.PrintSides.single, label: Text('Single')),
        ButtonSegment(value: print_option.PrintSides.double, label: Text('Double')),
      ],
      selected: {_printOption.sides},
      onSelectionChanged: (Set<print_option.PrintSides> selection) {
        _updateOption(_printOption.copyWith(sides: selection.first));
      },
    );
  }

  Widget _buildOrientationSelector() {
    return SegmentedButton<print_option.Orientation>(
      segments: const [
        ButtonSegment(value: print_option.Orientation.portrait, label: Text('Portrait')),
        ButtonSegment(value: print_option.Orientation.landscape, label: Text('Landscape')),
      ],
      selected: {_printOption.orientation},
      onSelectionChanged: (Set<print_option.Orientation> selection) {
        _updateOption(_printOption.copyWith(orientation: selection.first));
      },
    );
  }

  Widget _buildBindingSelector() {
    return Wrap(
      spacing: 8,
      runSpacing: 8,
      children: print_option.BindingType.values.map((binding) {
        final isSelected = _printOption.binding == binding;
        return FilterChip(
          label: Text(binding.name == 'none'
              ? 'None'
              : binding.name
                  .replaceAllMapped(RegExp(r'([A-Z])'), (m) => ' ${m[1]}')
                  .trim()),
          selected: isSelected,
          onSelected: (selected) {
            if (selected) {
              _updateOption(_printOption.copyWith(binding: binding));
            }
          },
        );
      }).toList(),
    );
  }
}

