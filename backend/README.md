# Local PHP Backend for Printing Service App

This backend uses PHP with MySQL (phpMyAdmin) for local file storage instead of Firebase Storage.

## Setup Instructions

### 1. Create Database

1. Open phpMyAdmin: http://localhost/phpmyadmin
2. Create a new database named `printing_service`
3. Or run the setup script:
   ```bash
   php backend/api/database.php
   ```

### 2. Configure Database Connection

Edit `backend/api/config.php` if your MySQL credentials are different:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');  // Your MySQL password
define('DB_NAME', 'printing_service');
```

### 3. Set Up File Upload Directory

The uploads directory will be created automatically at:
`backend/uploads/`

Make sure Apache/PHP has write permissions to this directory.

### 4. Configure Flutter App

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
// Use your computer's IP address
static const String baseUrl = 'http://192.168.1.xxx';  // Replace with your IP
```

### 5. Test the API

Access these endpoints in your browser:
- http://localhost/backend/api/database.php (creates database)
- http://localhost/backend/api/config.php (check config)

## API Endpoints

### Upload File
- **URL**: `POST /backend/api/upload_file.php`
- **Parameters**:
  - `file` (multipart/form-data): The file to upload
  - `user_id` (string): User ID
  - `order_id` (string, optional): Order ID
- **Response**:
```json
{
  "success": true,
  "file": {
    "id": "file_id",
    "name": "filename.pdf",
    "file_url": "/backend/uploads/user_id/order_id/file_id_filename.pdf",
    "size_bytes": 12345,
    "size_mb": "0.01",
    "file_type": "pdf",
    "page_count": 5,
    "uploaded_at": "2024-01-01T00:00:00+00:00"
  }
}
```

### Get File
- **URL**: `GET /backend/api/get_file.php?id={file_id}`
- **Response**: Same as upload response

### Delete File
- **URL**: `POST /backend/api/delete_file.php`
- **Body**: `{"id": "file_id"}`
- **Response**:
```json
{
  "success": true,
  "message": "File deleted successfully"
}
```

## Database Schema

The database includes these tables:
- `users` - User accounts
- `user_addresses` - User delivery addresses
- `uploaded_files` - File metadata
- `orders` - Order information
- `order_files` - Order-file relationships
- `order_options` - Print configuration
- `delivery_addresses` - Order delivery addresses

## File Storage Structure

```
backend/uploads/
  └── {user_id}/
      └── {order_id}/
          └── {file_id}_{filename}
```

## Troubleshooting

### Files not uploading
- Check Apache/PHP is running in XAMPP
- Verify upload directory permissions (should be writable)
- Check `upload_max_filesize` and `post_max_size` in php.ini
- Check error logs in XAMPP

### Database connection errors
- Verify MySQL is running
- Check database credentials in `config.php`
- Ensure database `printing_service` exists

### CORS errors
- Make sure `.htaccess` is in place
- Check Apache `mod_rewrite` is enabled
- Verify CORS headers in `config.php`

### Android Emulator can't connect
- Use `10.0.2.2` instead of `localhost`
- Check your computer's firewall

### Physical device can't connect
- Find your computer's IP: `ipconfig` (Windows) or `ifconfig` (Mac/Linux)
- Use that IP in `api_config.dart`
- Ensure device and computer are on same network
- Check firewall allows connections

## Next Steps

1. Create database and run setup
2. Update `api_config.dart` with correct URL
3. Test file upload from Flutter app
4. Verify files appear in `backend/uploads/`
5. Check database in phpMyAdmin

