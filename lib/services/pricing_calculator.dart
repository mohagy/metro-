import '../config/app_config.dart';
import '../models/print_file.dart';
import '../models/print_option.dart';

class PricingCalculator {
  static double calculateCost({
    required List<PrintFile> files,
    required PrintOption printOption,
  }) {
    // Calculate total pages
    int totalPages = 0;
    for (var file in files) {
      int filePages = file.pageCount ?? estimatePages(file);
      totalPages += filePages;
    }

    // Adjust for double-sided printing
    int effectivePages = printOption.sides == PrintSides.double
        ? (totalPages / 2).ceil()
        : totalPages;

    // Base cost per page
    double costPerPage = printOption.color == PrintColor.color
        ? AppConfig.colorPageCost
        : AppConfig.blackWhitePageCost;

    // Paper size multiplier
    double sizeMultiplier = AppConfig.paperSizeMultipliers[
            printOption.paperSizeLabel] ??
        1.0;

    // Calculate paper cost
    double paperCost =
        costPerPage * effectivePages * printOption.quantity * sizeMultiplier;

    // Binding cost
    double bindingCost = AppConfig.bindingCosts[printOption.bindingLabel] ?? 0.0;

    // Service fee
    double serviceFee = AppConfig.serviceFee;

    return paperCost + bindingCost + serviceFee;
  }

  static int estimatePages(PrintFile file) {
    // Estimate pages based on file size and type
    // This is a rough estimate - in production, you'd use a PDF library
    if (file.isPdf) {
      // Rough estimate: 1 page per 50KB for PDFs
      return ((file.sizeBytes / 1024) / 50).ceil().clamp(1, 1000);
    } else if (file.isImage) {
      // Images are typically 1 page
      return 1;
    } else {
      // Documents: estimate based on size
      return ((file.sizeBytes / 1024) / 30).ceil().clamp(1, 1000);
    }
  }

  static Map<String, double> getCostBreakdown({
    required List<PrintFile> files,
    required PrintOption printOption,
  }) {
    int totalPages = 0;
    for (var file in files) {
      int filePages = file.pageCount ?? estimatePages(file);
      totalPages += filePages;
    }

    int effectivePages = printOption.sides == PrintSides.double
        ? (totalPages / 2).ceil()
        : totalPages;

    double costPerPage = printOption.color == PrintColor.color
        ? AppConfig.colorPageCost
        : AppConfig.blackWhitePageCost;

    double sizeMultiplier = AppConfig.paperSizeMultipliers[
            printOption.paperSizeLabel] ??
        1.0;

    double paperCost =
        costPerPage * effectivePages * printOption.quantity * sizeMultiplier;
    double bindingCost = AppConfig.bindingCosts[printOption.bindingLabel] ?? 0.0;
    double serviceFee = AppConfig.serviceFee;

    return {
      'paperCost': paperCost,
      'bindingCost': bindingCost,
      'serviceFee': serviceFee,
      'total': paperCost + bindingCost + serviceFee,
      'pages': (effectivePages * printOption.quantity).toDouble(),
    };
  }
}

