import 'package:flutter/material.dart';
import '../models/print_option.dart';

class PrintOptionSelector extends StatelessWidget {
  final PrintOption currentOption;
  final Function(PrintOption) onChanged;

  const PrintOptionSelector({
    super.key,
    required this.currentOption,
    required this.onChanged,
  });

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.stretch,
      children: [
        // Paper Size
        _buildSectionTitle('Paper Size'),
        SegmentedButton<PaperSize>(
          segments: const [
            ButtonSegment(value: PaperSize.a4, label: Text('A4')),
            ButtonSegment(value: PaperSize.letter, label: Text('Letter')),
            ButtonSegment(value: PaperSize.legal, label: Text('Legal')),
            ButtonSegment(value: PaperSize.a3, label: Text('A3')),
          ],
          selected: {currentOption.paperSize},
          onSelectionChanged: (Set<PaperSize> selection) {
            onChanged(currentOption.copyWith(paperSize: selection.first));
          },
        ),
        const SizedBox(height: 16),

        // Color
        _buildSectionTitle('Print Color'),
        SegmentedButton<PrintColor>(
          segments: const [
            ButtonSegment(value: PrintColor.blackWhite, label: Text('B&W')),
            ButtonSegment(value: PrintColor.color, label: Text('Color')),
          ],
          selected: {currentOption.color},
          onSelectionChanged: (Set<PrintColor> selection) {
            onChanged(currentOption.copyWith(color: selection.first));
          },
        ),
      ],
    );
  }

  Widget _buildSectionTitle(String title) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 8),
      child: Text(
        title,
        style: const TextStyle(
          fontSize: 16,
          fontWeight: FontWeight.bold,
        ),
      ),
    );
  }
}

