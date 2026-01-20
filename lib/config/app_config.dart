class AppConfig {
  static const String appName = 'Print Service';
  
  // Pricing constants (in USD)
  static const double colorPageCost = 0.15;
  static const double blackWhitePageCost = 0.05;
  static const double serviceFee = 2.0;
  
  // Paper size multipliers
  static const Map<String, double> paperSizeMultipliers = {
    'A4': 1.0,
    'Letter': 1.0,
    'Legal': 1.2,
    'A3': 1.5,
    'A5': 0.8,
  };
  
  // Binding costs
  static const Map<String, double> bindingCosts = {
    'None': 0.0,
    'Staple': 1.0,
    'Spiral': 3.0,
    'Perfect Binding': 5.0,
  };
  
  // File size limits (in MB)
  static const int maxFileSizeMB = 50;
  static const int maxTotalFilesMB = 200;
  
  // Supported file types
  static const List<String> supportedFileTypes = [
    'pdf',
    'doc',
    'docx',
    'jpg',
    'jpeg',
    'png',
    'ppt',
    'pptx',
    'xls',
    'xlsx',
  ];
  
  // Order statuses
  static const String statusPending = 'Pending';
  static const String statusPrinting = 'Printing';
  static const String statusReady = 'Ready';
  static const String statusCompleted = 'Completed';
  static const String statusCancelled = 'Cancelled';
  
  // Delivery options
  static const String deliveryPickup = 'Pickup';
  static const String deliveryHome = 'Home Delivery';
  static const String deliveryOffice = 'Office Delivery';
  
  // Service categories
  static const List<String> serviceCategories = [
    'School',
    'Office',
    'Business',
    'Personal',
  ];
}

