# Web Version Setup Complete ✅

## Flutter Web App Created

Your Flutter printing service app is now configured for web deployment!

## What Was Set Up

### 1. Flutter Web Platform ✅
- Web platform enabled
- Web directory created with `index.html`, `manifest.json`, and icons

### 2. Firebase Web Configuration ✅
- Firebase web SDK configured in `web/index.html`
- Firebase options configured in `lib/firebase_options.dart`
- Main app updated to use web-specific Firebase config

### 3. API Configuration ✅
- Updated `lib/config/api_config.dart` to work with web
- Web version uses localhost for API calls

### 4. Web Files Created ✅
- `web/index.html` - Main HTML file with Firebase config
- `web/manifest.json` - PWA manifest
- `web/icons/` - App icons for web

## Running the Web App

### Development Mode

```bash
# Run in development mode (hot reload enabled)
flutter run -d chrome

# Or run in any browser
flutter run -d web-server --web-port=8080
```

Then open: http://localhost:8080

### Build for Production

```bash
# Build web app for production
flutter build web

# Build with specific base href (if deploying to subdirectory)
flutter build web --base-href="/printing-service/"
```

The built files will be in `build/web/` directory.

## Deploy Options

### Option 1: Deploy to Firebase Hosting

```bash
# Install Firebase CLI if not already installed
npm install -g firebase-tools

# Build the web app
flutter build web

# Initialize Firebase hosting (if not already done)
firebase init hosting

# Deploy
firebase deploy --only hosting
```

### Option 2: Deploy to XAMPP (Local)

1. Build the web app:
   ```bash
   flutter build web
   ```

2. Copy `build/web/` contents to your XAMPP htdocs:
   ```bash
   # Copy to XAMPP
   xcopy /E /I build\web C:\xampp2\htdocs\printing-service
   ```

3. Access at: http://localhost/printing-service/

### Option 3: Deploy to Any Web Server

1. Build the web app:
   ```bash
   flutter build web
   ```

2. Upload `build/web/` contents to your web server

3. Configure your server to serve the files

## Firebase Configuration

Your Firebase web app is configured with:
- **API Key**: `AIzaSyC3Er5dy-dszTP3swA7_frXMV_M5iwAyZg`
- **Project ID**: `printing-service-app-8949`
- **App ID**: `1:202313864742:web:f5185ad2a864f5fa95f8e7`

## Features Available on Web

✅ User authentication (Firebase Auth)  
✅ File upload (PHP backend or Firebase Storage)  
✅ Order management  
✅ Real-time cost calculator  
✅ Order tracking with QR codes  
✅ Order history  
✅ Profile management  

## API Configuration for Web

The web version is configured to use:
- **Local Development**: `http://localhost/backend/api`
- **Production**: Update `lib/config/api_config.dart` baseUrl

## Testing

### Test Locally

1. Make sure XAMPP is running (Apache + MySQL)
2. Run Flutter web app:
   ```bash
   flutter run -d chrome
   ```
3. Test features:
   - User registration/login
   - File upload
   - Order creation
   - Order tracking

### Test Production Build

1. Build web app:
   ```bash
   flutter build web
   ```

2. Serve locally:
   ```bash
   cd build/web
   python -m http.server 8000
   ```

3. Open: http://localhost:8000

## Troubleshooting

### CORS Errors
- Make sure your PHP backend has CORS headers (already configured in `backend/api/config.php`)
- Check `.htaccess` is in place

### Firebase Not Loading
- Check browser console for errors
- Verify Firebase config in `lib/firebase_options.dart`
- Make sure Firebase services are enabled in Firebase Console

### API Not Connecting
- Update `lib/config/api_config.dart` with correct URL
- Check that backend is running in XAMPP
- Verify database is set up

### Build Errors
```bash
flutter clean
flutter pub get
flutter build web
```

## File Structure

```
web/
├── index.html          ✅ Main HTML file (Firebase config)
├── manifest.json       ✅ PWA manifest
├── favicon.png         ✅ Favicon
└── icons/              ✅ App icons
    ├── Icon-192.png
    ├── Icon-512.png
    └── ...
```

## Next Steps

1. ✅ Web platform enabled - **DONE**
2. ✅ Firebase configured - **DONE**
3. ⏭️ Test the web app: `flutter run -d chrome`
4. ⏭️ Build for production: `flutter build web`
5. ⏭️ Deploy to hosting

## Quick Start

```bash
# Run in Chrome
flutter run -d chrome

# Build for production
flutter build web

# Deploy to Firebase Hosting
firebase deploy --only hosting
```

Your web app is ready to run! 🚀

