# Local Storage Setup (phpMyAdmin/MySQL)

## ✅ What Was Created

### PHP Backend API
- ✅ `backend/api/config.php` - Database and upload configuration
- ✅ `backend/api/database.php` - Database schema creation
- ✅ `backend/api/upload_file.php` - File upload endpoint
- ✅ `backend/api/get_file.php` - Get file information
- ✅ `backend/api/delete_file.php` - Delete file endpoint
- ✅ `backend/.htaccess` - Apache configuration

### Flutter Updates
- ✅ `lib/config/api_config.dart` - API configuration
- ✅ `lib/services/file_upload_service.dart` - Updated to use PHP API

## Quick Setup Steps

### 1. Create Database
```bash
# Option 1: Run PHP script
php backend/api/database.php

# Option 2: Use phpMyAdmin
# Go to http://localhost/phpmyadmin
# Create database: printing_service
# Import or run SQL from database.php
```

### 2. Update API URL in Flutter

Edit `lib/config/api_config.dart`:

**For Android Emulator:**
```dart
static const String baseUrl = 'http://10.0.2.2';
```

**For Physical Device:**
```dart
// Find your IP: ipconfig (Windows)
static const String baseUrl = 'http://192.168.1.xxx';
```

### 3. Test

1. Start XAMPP (Apache + MySQL)
2. Run Flutter app
3. Try uploading a file
4. Check `backend/uploads/` directory
5. Check database in phpMyAdmin

## Project Structure

```
backend/
├── api/
│   ├── config.php           ✅ Database & upload config
│   ├── database.php         ✅ Creates database schema
│   ├── upload_file.php      ✅ Handles file uploads
│   ├── get_file.php         ✅ Retrieves file info
│   └── delete_file.php      ✅ Deletes files
├── uploads/                 ✅ File storage (auto-created)
└── .htaccess                ✅ Apache config
```

## Database Tables

The following tables are created automatically:
- `users` - User accounts
- `user_addresses` - Delivery addresses
- `uploaded_files` - File metadata
- `orders` - Print orders
- `order_files` - Order-file links
- `order_options` - Print settings
- `delivery_addresses` - Order addresses

## File Storage Location

Files are stored in: `backend/uploads/{user_id}/{order_id}/`

This matches the structure Firebase Storage would have used, making it easy to switch back if needed.

## Benefits of Local Storage

✅ No Firebase Storage needed  
✅ Full control over files  
✅ Easy access via phpMyAdmin  
✅ Can view/backup files directly  
✅ No cloud storage costs  
✅ Works offline (locally)  
✅ Fast local network access  

## API Endpoints

- **Upload**: `POST /backend/api/upload_file.php`
- **Get**: `GET /backend/api/get_file.php?id={id}`
- **Delete**: `POST /backend/api/delete_file.php`

See `backend/README.md` for detailed API documentation.

