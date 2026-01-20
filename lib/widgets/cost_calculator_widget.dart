import 'package:flutter/material.dart';
import '../models/print_file.dart';
import '../models/print_option.dart';
import '../services/pricing_calculator.dart';
import '../utils/currency_formatter.dart';
import '../utils/constants.dart';

class CostCalculatorWidget extends StatelessWidget {
  final List<PrintFile> files;
  final PrintOption printOption;

  const CostCalculatorWidget({
    super.key,
    required this.files,
    required this.printOption,
  });

  @override
  Widget build(BuildContext context) {
    final breakdown = PricingCalculator.getCostBreakdown(
      files: files,
      printOption: printOption,
    );

    return Card(
      child: Padding(
        padding: const EdgeInsets.all(AppConstants.paddingMedium),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              'Cost Breakdown',
              style: Theme.of(context).textTheme.titleMedium?.copyWith(
                    fontWeight: FontWeight.bold,
                  ),
            ),
            const SizedBox(height: 16),
            _buildCostRow(
              context,
              'Paper Cost',
              breakdown['paperCost'] ?? 0.0,
            ),
            if ((breakdown['bindingCost'] ?? 0.0) > 0)
              _buildCostRow(
                context,
                'Binding',
                breakdown['bindingCost'] ?? 0.0,
              ),
            _buildCostRow(
              context,
              'Service Fee',
              breakdown['serviceFee'] ?? 0.0,
            ),
            const Divider(),
            _buildCostRow(
              context,
              'Total',
              breakdown['total'] ?? 0.0,
              isTotal: true,
            ),
            const SizedBox(height: 8),
            Text(
              '${breakdown['pages']?.toInt() ?? 0} pages × ${printOption.quantity} copies',
              style: Theme.of(context).textTheme.bodySmall?.copyWith(
                    color: Colors.grey,
                  ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildCostRow(BuildContext context, String label, double amount,
      {bool isTotal = false}) {
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
            CurrencyFormatter.format(amount),
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
}

