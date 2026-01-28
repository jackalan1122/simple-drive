# Admin Panel Integration

## Overview
A complete admin management system has been integrated into Simple Drive. This allows administrators to:
- View system statistics (users, files, storage)
- Manage users (view, delete, assign/revoke admin privileges)
- Manage files (delete user files, manage storage)
- Monitor system activity

## What Was Added

### 1. Database Changes
**File: `db.sql`**
- Added `is_admin TINYINT(1) DEFAULT 0` column to the `users` table
- This tracks which users have admin privileges

### 2. Configuration Updates
**File: `config.php`**
- Added `isAdmin()` function - checks if current user is an admin
- Added `requireAdmin()` function - blocks non-admin access to admin pages

### 3. Authentication Updates
**File: `login.php`**
- Modified to load `is_admin` status from database into session
- Admin status is now available as `$_SESSION['is_admin']`

### 4. Admin Dashboard
**New File: `admin.php`**
Complete admin control panel with:

#### Features:
- **Dashboard Statistics**: 
  - Total users count
  - Total admin users count
  - Total files count
  - Total storage used

- **User Management Tab**:
  - View all users with username, email, role, join date
  - Delete users (with confirmation modal)
  - Promote/demote users to/from admin status
  - Prevents deletion of the current admin user
  - Prevents demotion of the last admin

- **File Management Tab**:
  - View last 50 uploaded files
  - Shows file owner, size, upload date
  - Delete any file from the system
  - Deletes both database entry and physical file

- **Actions Protected**:
  - Confirmation modals for destructive actions
  - Success/error messages for all operations
  - Data validation on all inputs

### 5. User Dashboard Updates
**File: `index.php`**
- Added admin panel button to the header
- Button only shows for users with admin privileges
- Styled with "⚙️ Admin Panel" icon

### 6. Admin Setup Tool
**New File: `setup-admin.php`**
- One-time setup utility to designate the first admin
- Allows converting any existing user to an admin
- Should be deleted after creating the first admin for security

## Usage Instructions

### Step 1: Update Database
After backing up your database, run the updated SQL schema:

```bash
mysql -u root -p < db.sql
```

Or if you already have a database, run this command in phpMyAdmin:

```sql
ALTER TABLE users ADD COLUMN is_admin TINYINT(1) DEFAULT 0;
```

### Step 2: Create First Admin
1. Open your browser and go to: `http://localhost/drive/setup-admin.php`
2. Enter the username of an existing user
3. Click "Make Admin"
4. **Delete `setup-admin.php` after completing this step for security**

### Step 3: Access Admin Panel
1. Log in with your admin account
2. Click the "⚙️ Admin Panel" button in the header
3. Start managing users and files

## File Structure
```
drive/
├── admin.php                    # Admin panel dashboard (NEW)
├── setup-admin.php             # First admin setup tool (NEW)
├── config.php                  # Updated with admin helpers
├── login.php                   # Updated to load is_admin status
├── index.php                   # Updated with admin button
├── db.sql                      # Updated database schema
└── [other files unchanged]
```

## Admin Panel Sections

### Dashboard Statistics
Four cards showing:
- **Total Users**: Count of all registered users
- **Admin Users**: Count of users with admin privileges
- **Total Files**: Count of all uploaded files
- **Total Storage**: Total size of all uploaded files

### User Management
Interactive table showing:
- Username
- Email address
- Current role (Admin/User badge)
- Join date
- Action buttons:
  - **Make Admin** / **Remove Admin** - Toggle admin status
  - **Delete** - Remove user from system

### File Management
Interactive table showing last 50 files:
- Original filename
- File owner (username)
- File size
- Upload date
- **Delete** button for removing files

## Security Features

1. **Admin-Only Access**: All admin operations require `requireAdmin()` check
2. **Admin Preservation**: Prevents deletion of the last admin user
3. **Session Validation**: Admin status verified from database during login
4. **CSRF Protection**: Forms use POST method for state-changing operations
5. **Confirmation Modals**: Destructive actions require explicit confirmation
6. **XSS Prevention**: All user input is HTML-escaped using `htmlspecialchars()`

## Admin Functions

### isAdmin()
Returns `true` if current user is an admin
```php
if (isAdmin()) {
    // User is admin
}
```

### requireAdmin()
Blocks access if user is not an admin (dies with 403 error)
```php
requireAdmin(); // Must be called at start of admin pages
```

## Permissions Reference

| Action | User | Admin |
|--------|------|-------|
| View Own Files | ✓ | ✓ |
| Upload Files | ✓ | ✓ |
| Delete Own Files | ✓ | ✓ |
| **View All Users** | ✗ | ✓ |
| **Delete Any User** | ✗ | ✓ |
| **Manage Admin Status** | ✗ | ✓ |
| **View All Files** | ✗ | ✓ |
| **Delete Any File** | ✗ | ✓ |
| **Access Admin Panel** | ✗ | ✓ |

## Important Notes

⚠️ **Security Warning**: 
- Delete `setup-admin.php` after creating the first admin
- Keep admin panel URL private or add additional security layers
- Regularly audit admin actions
- Backup database before making changes

## Troubleshooting

**"Access denied. Admin privileges required."**
- You must be logged in as an admin user
- Check that `is_admin` is set to 1 in the database

**Admin button not showing in dashboard**
- Make sure `login.php` is updated to load `is_admin` field
- Clear browser session/cookies and log in again

**Database error after updating schema**
- Ensure you ran the SQL update to add the `is_admin` column
- Check database user has ALTER TABLE permissions

## Future Enhancements

Potential additions to consider:
- Activity logs for all admin actions
- User quota/storage limits
- File type restrictions
- Admin role levels (super admin, moderator, etc.)
- Bulk user/file operations
- System backup/restore functionality
- Security audit trail
- Email notifications for admin actions
