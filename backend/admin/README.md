# Admin Panel Setup Guide

## Initial Setup

1. **Create Admin Database Tables**

   Run the setup script to create the admin tables and default admin user:
   
   ```bash
   # From the project root
   cd backend/api/admin
   php setup_admin_tables.php
   ```
   
   Or if using XAMPP, navigate to:
   ```
   http://localhost/metro/backend/api/admin/setup_admin_tables.php
   ```
   
   This will create:
   - `admin_users` table
   - `pricing_config` table
   - `paper_size_multipliers` table
   - `binding_costs` table
   - `admin_sessions` table (created automatically on first login)
   - Default admin user: `admin` / `admin123`

2. **Access the Admin Panel**

   Navigate to:
   ```
   http://localhost/metro/backend/admin/index.html
   ```

3. **Login Credentials**

   - Username: `admin`
   - Password: `admin123`
   
   **Important:** Change the default password after first login!

## Features

### Dashboard
- View total orders, revenue, users
- Today's statistics
- Orders breakdown by status

### Orders Management
- View all orders
- Search orders by ID, status, user, date range
- Update order status
- View detailed order information

### Users Management
- View all users
- Create new users
- Edit user information
- Delete users (if they have no orders)

### Pricing Configuration
- Configure base pricing (color/black-white page costs, service fees)
- Set paper size multipliers
- Configure binding costs
- All changes are saved immediately

## API Endpoints

All admin API endpoints are located in `/metro/backend/api/admin/`:

### Authentication
- `login.php` - Admin login
- `logout.php` - Admin logout
- `verify_session.php` - Verify admin session

### Orders
- `get_orders.php` - Get all orders (paginated)
- `get_order.php` - Get single order details
- `update_order_status.php` - Update order status
- `search_orders.php` - Search orders with filters
- `get_orders_by_status.php` - Get orders by status

### Users
- `get_users.php` - Get all users (paginated)
- `get_user.php` - Get single user details
- `create_user.php` - Create new user
- `update_user.php` - Update user information
- `delete_user.php` - Delete user

### Pricing
- `get_pricing.php` - Get all pricing configuration
- `update_pricing.php` - Update pricing value
- `get_paper_multipliers.php` - Get paper size multipliers
- `update_paper_multiplier.php` - Update paper multiplier
- `get_binding_costs.php` - Get binding costs
- `update_binding_cost.php` - Update binding cost

### Statistics
- `get_stats.php` - Get dashboard statistics

## Security Notes

- All endpoints require authentication (except login)
- Sessions expire after 24 hours
- Passwords are hashed using PHP's `password_hash()`
- SQL injection protection via prepared statements
- CORS is configured in `config.php`

## Customization

### Change API Base URL

Edit `backend/admin/js/admin-api.js`:
```javascript
const API_BASE_URL = '/metro/backend/api/admin';
```

### Change Default Admin Password

After logging in, you can create a new admin user or update the existing one in the database:
```sql
UPDATE admin_users 
SET password_hash = PASSWORD_HASH('your_new_password', PASSWORD_DEFAULT) 
WHERE username = 'admin';
```

## Troubleshooting

### "Cannot connect to backend"
- Ensure XAMPP is running
- Check that the API base URL in `admin-api.js` matches your setup
- Verify CORS headers in `backend/api/config.php`

### "Invalid or expired session"
- Log out and log back in
- Clear browser localStorage
- Check that `admin_sessions` table exists

### "Table doesn't exist"
- Run `setup_admin_tables.php` again
- Check database connection in `backend/api/config.php`

