# Local Storage Setup Guide (phpMyAdmin/MySQL)

## ✅ What's Been Created

I've set up a **local PHP backend** that uses **phpMyAdmin/MySQL** for storage instead of Firebase Storage. The Flutter app will now upload files to your local XAMPP server.

## Setup Steps

### Step 1: Create Database in phpMyAdmin

1. Open phpMyAdmin: http://localhost/phpmyadmin
2. Click "New" in the left sidebar
3. Database name: `printing_service`
4. Collation: `utf8mb4_general_ci`
5. Click "Create"

### Step 2: Create Tables

1. Select the `printing_service` database
2. Click on "SQL" tab
3. Copy and paste the SQL from `backend/api/database.php` (the CREATE TABLE statements)
4. Or run this command if PHP is in your PATH:
   ```bash
   php backend/api/database.php
   ```

**Note:** If you get "database doesn't exist" error, make sure you created it first in Step 1.

### Step 3: Update Flutter API URL

Edit `lib/config/api_config.dart`:

**For Android Emulator:**
```dart
static const String baseUrl = 'http://10.0.2.2';
```

**For iOS Simulator:**
```dart
static const String baseUrl = 'http://localhost';
```

**For Physical Device:**
```dart
// Find your IP address:
// Windows: ipconfig
// Mac/Linux: ifconfig
// Then use: 'http://192.168.1.xxx' (replace xxx with your IP)
static const String baseUrl = 'http://192.168.1.xxx';
```

### Step 4: Verify XAMPP is Running

Make sure XAMPP has these services running:
- ✅ **Apache** (for PHP backend)
- ✅ **MySQL** (for database)

### Step 5: Test the Setup

1. Start XAMPP (Apache + MySQL)
2. Open: http://localhost/backend/api/database.php
3. You should see: "Database and tables created successfully!"
4. Check phpMyAdmin - you should see the `printing_service` database with tables

### Step 6: Test File Upload

1. Run your Flutter app
2. Try uploading a file
3. Check `backend/uploads/` folder - files should appear there
4. Check database in phpMyAdmin - file info should be in `uploaded_files` table

## File Structure

```
backend/
├── api/
│   ├── config.php           ✅ Database configuration
│   ├── database.php         ✅ Creates tables (run once)
│   ├── upload_file.php      ✅ Handles file uploads
│   ├── get_file.php         ✅ Get file info
│   └── delete_file.php      ✅ Delete files
├── uploads/                 ✅ Files stored here (auto-created)
│   └── {user_id}/
│       └── {order_id}/
│           └── files...
└── .htaccess                ✅ Apache configuration
```

## Database Tables Created

- `users` - User accounts
- `user_addresses` - Delivery addresses  
- `uploaded_files` - File metadata
- `orders` - Print orders
- `order_files` - Links orders to files
- `order_options` - Print settings
- `delivery_addresses` - Order delivery info

## Troubleshooting

### Database Connection Error
- **Solution**: Check MySQL is running in XAMPP
- Verify database `printing_service` exists in phpMyAdmin
- Check credentials in `backend/api/config.php`

### Files Not Uploading
- **Solution**: Check Apache is running in XAMPP
- Verify `backend/uploads/` directory exists and is writable
- Check PHP error logs in XAMPP

### Flutter Can't Connect
- **Android Emulator**: Use `10.0.2.2` instead of `localhost`
- **Physical Device**: Use your computer's IP address
- Check firewall allows connections
- Ensure device and computer are on same network

### Permission Denied
- **Solution**: Make sure `backend/uploads/` has write permissions
- On Windows, right-click folder > Properties > Security > Allow write

## What Changed in Flutter

### Updated Files:
- ✅ `lib/services/file_upload_service.dart` - Now uses HTTP API instead of Firebase Storage
- ✅ `lib/config/api_config.dart` - New file with API configuration

### What You Need to Do:
1. ✅ Create database in phpMyAdmin
2. ✅ Update API URL in `api_config.dart`
3. ✅ Test file upload

## Benefits

✅ No Firebase Storage needed  
✅ Files stored locally on your XAMPP server  
✅ View files in `backend/uploads/` folder  
✅ Manage data in phpMyAdmin  
✅ No cloud storage costs  
✅ Full control over your data  
✅ Works with existing XAMPP setup  

## Next Steps

1. Create database (Step 1)
2. Run database setup (Step 2)
3. Update API URL (Step 3)
4. Test the app!

Files will now be uploaded to your local XAMPP server instead of Firebase Storage!

