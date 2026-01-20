# Quick Start Guide

## Prerequisites

1. Flutter SDK (3.0.0 or higher)
2. Firebase account
3. Android Studio / Xcode (for mobile development)

## Setup Steps

### 1. Install Dependencies

```bash
flutter pub get
```

### 2. Firebase Configuration

Follow the instructions in `firebase_setup.md` to:
- Create a Firebase project
- Configure Android and iOS
- Enable required services (Auth, Firestore, Storage, Messaging)
- Add security rules

### 3. Run the App

```bash
# For Android
flutter run

# For iOS (Mac only)
flutter run -d ios

# For specific device
flutter devices
flutter run -d <device-id>
```

## App Features

### User Features
- ✅ User Registration & Login
- ✅ Password Reset
- ✅ Document Upload (Multiple file types)
- ✅ Print Configuration
  - Paper size (A4, Letter, Legal, A3, A5)
  - Color/B&W printing
  - Quantity selection
  - Single/Double-sided
  - Orientation (Portrait/Landscape)
  - Binding options
- ✅ Real-time Cost Calculator
- ✅ Order Placement
- ✅ Order Tracking with QR Code
- ✅ Order History
- ✅ Profile Management

### Business Features
- ✅ Order Management
- ✅ Status Tracking
- ✅ Cost Calculation
- ✅ File Storage

## Project Structure

```
lib/
├── main.dart                 # App entry point
├── config/
│   └── app_config.dart      # App configuration & constants
├── models/                  # Data models
│   ├── user_model.dart
│   ├── print_file.dart
│   ├── print_option.dart
│   └── print_order.dart
├── services/               # Business logic & Firebase
│   ├── auth_service.dart
│   ├── file_upload_service.dart
│   ├── pricing_calculator.dart
│   ├── order_service.dart
│   └── notification_service.dart
├── providers/             # State management
│   ├── auth_provider.dart
│   ├── cart_provider.dart
│   └── order_provider.dart
├── screens/              # UI Screens
│   ├── auth/
│   │   ├── auth_wrapper.dart
│   │   ├── login_screen.dart
│   │   ├── register_screen.dart
│   │   └── forgot_password_screen.dart
│   ├── home_screen.dart
│   ├── upload_screen.dart
│   ├── configure_screen.dart
│   ├── checkout_screen.dart
│   ├── tracking_screen.dart
│   ├── order_history_screen.dart
│   └── profile_screen.dart
├── widgets/             # Reusable widgets
│   ├── cost_calculator_widget.dart
│   ├── order_card.dart
│   ├── qr_code_widget.dart
│   ├── status_indicator.dart
│   ├── file_upload_widget.dart
│   └── print_option_selector.dart
└── utils/              # Utilities
    ├── constants.dart
    ├── validators.dart
    ├── currency_formatter.dart
    └── order_id_generator.dart
```

## Testing

### Test Users
1. Register a new account through the app
2. Login with your credentials

### Test Order Flow
1. Navigate to "Print Documents"
2. Select files to upload
3. Configure print options
4. Review and place order
5. Track order status
6. View order history

## Customization

### Pricing
Edit `lib/config/app_config.dart` to adjust:
- Page costs (color vs B&W)
- Service fees
- Binding costs
- Paper size multipliers

### File Limits
Edit `lib/config/app_config.dart`:
- `maxFileSizeMB`: Maximum file size
- `maxTotalFilesMB`: Maximum total size
- `supportedFileTypes`: Allowed file extensions

## Troubleshooting

### Firebase Errors
- Ensure `google-services.json` is in `android/app/`
- Ensure `GoogleService-Info.plist` is in `ios/Runner/`
- Verify Firebase services are enabled in console
- Check security rules

### Build Errors
```bash
flutter clean
flutter pub get
flutter run
```

### Permission Issues
- Check Android manifest permissions
- Verify iOS permissions in Info.plist

## Next Steps

1. Add payment integration (Stripe, PayPal, etc.)
2. Implement push notifications fully
3. Add admin panel for order management
4. Add order cancellation
5. Implement reorder functionality
6. Add document preview
7. Add multi-language support
8. Add dark mode toggle

## Support

For issues or questions:
1. Check Firebase console for errors
2. Review Flutter logs: `flutter logs`
3. Verify all dependencies: `flutter pub deps`

