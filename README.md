# Printing Service App

A Flutter web application for printing services business with Firebase integration and PHP backend.

## Features

- File upload and management
- Order creation and tracking
- User authentication (Firebase)
- QR code generation for order pickup
- Admin panel for order management
- Real-time order status updates

## Tech Stack

- **Frontend**: Flutter Web
- **Backend**: PHP (XAMPP)
- **Database**: MySQL + Firebase Firestore
- **Authentication**: Firebase Auth
- **Storage**: Local PHP backend

## Getting Started

### Prerequisites

- Flutter SDK (3.0.0+)
- XAMPP (for PHP backend)
- Firebase project
- MySQL database

### Installation

1. Clone the repository:
   ```bash
   git clone https://github.com/YOUR_USERNAME/metro.git
   cd metro
   ```

2. Install Flutter dependencies:
   ```bash
   flutter pub get
   ```

3. Set up Firebase:
   - Follow instructions in `firebase_setup.md`
   - Configure Firebase in `lib/firebase_options.dart`

4. Set up PHP backend:
   - Start XAMPP
   - Import database from `backend/api/database.php`
   - Configure `backend/api/config.php`

5. Run the app:
   ```bash
   flutter run -d chrome
   ```

## Deployment

### GitHub Pages

See `DEPLOYMENT.md` for detailed deployment instructions.

Quick deploy:
```bash
flutter build web --release --base-href "/metro/"
# Then push to GitHub and enable Pages
```

### Backend Deployment

The PHP backend needs to be hosted separately (GitHub Pages only supports static files).

Options:
- Heroku
- Railway
- Render
- Your own server

## Project Structure

```
metro/
├── lib/              # Flutter app source code
├── backend/          # PHP backend
│   ├── api/         # API endpoints
│   └── admin/       # Admin panel
├── web/             # Web assets
└── build/           # Build output
```

## Documentation

- `DEPLOYMENT.md` - Deployment guide
- `FIREBASE_ADMIN_SETUP.md` - Firebase Admin SDK setup
- `backend/admin/README.md` - Admin panel documentation

## License

[Your License Here]
