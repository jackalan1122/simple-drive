# Simple Drive - PHP & MySQL File Storage System

A simple and clean file storage system built with PHP and MySQL that allows users to upload, download, and manage their files.

## Features

- **User Authentication**: Secure registration and login system
- **File Upload**: Upload files up to 500MB (configurable)
- **Real-time Progress Bar**: Visual upload progress with speed and time remaining
- **AJAX Upload**: Non-blocking uploads with live feedback
- **File Preview**: View files directly in browser without downloading
  - 🎥 **Videos**: MP4, WebM, OGG, MOV, AVI, MKV with player controls
  - 🖼️ **Images**: JPG, PNG, GIF, WebP, SVG with zoom controls
  - 🎵 **Audio**: MP3, WAV, OGG, AAC, FLAC with player controls
  - 📕 **PDFs**: Full document preview with navigation
  - 📝 **Text Files**: TXT, MD, JSON, CSV, HTML, CSS, JS, code files
- **Smart File Icons**: Different icons for videos, images, audio, PDFs, text, and archives
- **Video Streaming**: HTTP range requests support for seeking/skipping
- **File Management**: View, download, and delete files
- **Storage Stats**: Track total files and storage used
- **Secure Access**: Users can only access their own files
- **Responsive Design**: Clean, modern interface

## Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache/Nginx web server
- mod_rewrite enabled (for Apache)

## Installation

### 1. Set up the database

```bash
# Login to MySQL
mysql -u root -p

# Create the database and tables
mysql -u root -p < database.sql
```

Or manually run the SQL commands in `database.sql` file through phpMyAdmin.

### 2. Configure the application

Edit `config.php` and update the database credentials:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');
define('DB_NAME', 'simple_drive');
```

### 3. Set up file permissions

```bash
# Create uploads directory and set permissions
mkdir uploads
chmod 755 uploads
```

### 4. Configure PHP

**IMPORTANT:** For 500MB file uploads, you MUST update your PHP configuration.

Edit your `php.ini` file and set these values:

```ini
upload_max_filesize = 500M
post_max_size = 520M
max_execution_time = 600
max_input_time = 600
memory_limit = 256M
```

**Note:** The .htaccess file includes these settings, but they may not work on all servers. 
See `PHP_CONFIGURATION.md` for detailed instructions on finding and editing php.ini 
for different server environments (XAMPP, WAMP, MAMP, Linux).

To verify your settings work, create a `phpinfo.php` file:
```php
<?php phpinfo(); ?>
```
Visit it in your browser and search for the above settings. Delete the file after checking.

### 5. Deploy to web server

Copy all files to your web server's document root (e.g., `/var/www/html/` or `htdocs`).

### 6. Access the application

Open your browser and navigate to:
```
http://localhost/register.php
```

## File Structure

```
simple-drive/
├── config.php              # Database configuration
├── database.sql            # Database schema
├── register.php            # User registration page
├── login.php              # User login page
├── index.php              # Main drive interface
├── upload.php             # File upload handler (AJAX JSON response)
├── download.php           # File download handler (binary-safe)
├── stream.php             # File streaming handler (range requests)
├── player.php             # Universal file preview page
├── delete.php             # File delete handler
├── logout.php             # Logout handler
├── .htaccess              # Apache configuration
├── uploads/               # File storage directory (created automatically)
├── README.md              # This file
├── PHP_CONFIGURATION.md   # PHP setup guide for large uploads
├── VIDEO_TROUBLESHOOTING.md  # Video-specific troubleshooting
└── SUPPORTED_FILE_TYPES.md   # Complete file types reference
```

## Configuration Options

In `config.php`, you can adjust:

- `MAX_FILE_SIZE`: Maximum file upload size (default: 500MB)
- `UPLOAD_DIR`: Directory where files are stored
- Database connection settings

**Remember:** If you change `MAX_FILE_SIZE`, you must also update your `php.ini` 
settings to match (or exceed) this value.

## Security Features

- Password hashing using `password_hash()`
- Prepared statements to prevent SQL injection
- Session-based authentication
- User file isolation (users can only access their own files)
- Unique filename generation to prevent conflicts
- File type and size validation

## Usage

1. **Register**: Create a new account at `register.php`
2. **Login**: Sign in at `login.php`
3. **Upload**: Click the upload area and select a file (up to 500MB)
4. **Monitor Progress**: Watch the real-time progress bar showing upload percentage, speed, and time remaining
5. **Preview**: Click the preview button (▶ Play, 👁 View, 🔊 Play, 📖 View, 📝 View) to view files in browser
   - Videos: Play with controls, seek/skip through video
   - Images: View with zoom in/out controls
   - Audio: Play with controls
   - PDFs: Read with built-in viewer
   - Text Files: View formatted content
6. **Download**: Click the download button to save files to your device
7. **Delete**: Click the delete button to remove a file

## Customization

### Change Maximum File Size

Edit `config.php`:
```php
define('MAX_FILE_SIZE', 1024 * 1024 * 1024); // 1GB
```

**CRITICAL:** Also update your PHP configuration in `php.ini`:
```ini
upload_max_filesize = 1024M  ; Must be >= MAX_FILE_SIZE
post_max_size = 1050M        ; Must be slightly larger than upload_max_filesize
```

And in `.htaccess` (if using Apache):
```apache
php_value upload_max_filesize 1024M
php_value post_max_size 1050M
```

See `PHP_CONFIGURATION.md` for detailed instructions.

### Change Upload Directory

Edit `config.php`:
```php
define('UPLOAD_DIR', '/path/to/your/uploads/');
```

### Styling

All CSS is embedded in the PHP files. You can modify the `<style>` sections in each file to customize the appearance.

## Troubleshooting

### Files not uploading
- Check PHP upload settings in `php.ini` (see `PHP_CONFIGURATION.md`)
- Verify `uploads/` directory permissions (should be 755)
- Check web server error logs
- For large files, ensure `max_execution_time` and `max_input_time` are sufficient

### Progress bar not showing or freezing
- This is normal behavior - the progress bar tracks the upload to the server
- Browser uploads happen first, then server processing occurs
- For very large files, there may be a delay after 100% while the server processes
- Ensure JavaScript is enabled in your browser

### Upload times out
- Increase `max_execution_time` and `max_input_time` in php.ini
- Check if your hosting provider has additional timeout limits
- Consider using chunked uploads for files > 100MB in production

### Database connection fails
- Verify MySQL credentials in `config.php`
- Ensure MySQL service is running
- Check if database exists

### Session issues
- Ensure sessions are enabled in PHP
- Check session save path permissions

## Security Recommendations for Production

1. Use HTTPS for all connections
2. Store uploaded files outside the web root
3. Implement rate limiting for uploads
4. Add CSRF protection
5. Implement file type restrictions
6. Add virus scanning for uploaded files
7. Use environment variables for sensitive configuration
8. Implement proper logging
9. Add brute force protection on login
10. Regular security updates

## License

Free to use and modify for personal and commercial projects.

## Support

For issues or questions, please refer to the code comments or create an issue in your repository.