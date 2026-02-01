# Shared Hosting Deployment Guide

This guide will help you deploy this website to shared hosting (cPanel, Plesk, etc.).

## Pre-Deployment Checklist

- [ ] Database credentials from your hosting provider
- [ ] FTP/cPanel File Manager access
- [ ] Domain name configured
- [ ] PHP version 7.4 or higher available
- [ ] MySQL/MariaDB database created

## Step 1: Database Setup

1. **Create Database via cPanel:**
   - Login to cPanel
   - Go to "MySQL Databases"
   - Create a new database (e.g., `username_dealer`)
   - Create a database user and assign it to the database
   - Note down: Database name, Username, Password, Host (usually `localhost`)

2. **Import Database Schema:**
   - Go to phpMyAdmin in cPanel
   - Select your database
   - Click "Import"
   - Choose `database/schema.sql` from your project
   - Click "Go"

## Step 2: Upload Files

### Option A: Using FTP (Recommended)
1. Connect via FTP client (FileZilla, WinSCP, etc.)
2. Upload all files to `public_html/` (or `www/` or `htdocs/` depending on your host)
3. Maintain the folder structure exactly as it is

### Option B: Using cPanel File Manager
1. Login to cPanel → File Manager
2. Navigate to `public_html/`
3. Upload all files (you may need to zip first, then extract)

**Important:** Upload the entire project structure, including:
- `.htaccess` file
- `config/` folder
- `public/` folder
- `admin/` folder
- `uploads/` folder
- All other folders and files

## Step 3: Configure Database Connection

1. Edit `config/database.php` via cPanel File Manager or FTP:
   ```php
   define('DB_HOST', 'localhost'); // Usually 'localhost' on shared hosting
   define('DB_NAME', 'your_database_name'); // Your actual database name
   define('DB_USER', 'your_database_user'); // Your database username
   define('DB_PASS', 'your_database_password'); // Your database password
   define('DB_CHARSET', 'utf8mb4');
   ```

## Step 4: Configure Site URL

The `SITE_URL` is now auto-detected, but if you have issues:

1. Edit `config/config.php`
2. Find the `SITE_URL` section
3. Uncomment and set manually:
   ```php
   define('SITE_URL', 'https://yourdomain.com');
   ```
   Or for subdirectory:
   ```php
   define('SITE_URL', 'https://yourdomain.com/subfolder');
   ```

## Step 5: Set File Permissions

Set proper permissions via cPanel File Manager or FTP:

1. **Folders:** `755` (drwxr-xr-x)
   - `uploads/` and all subfolders
   - `config/` (if needed)

2. **Files:** `644` (-rw-r--r--)
   - All PHP files
   - `.htaccess`

3. **Specific folders that need write access:**
   ```bash
   uploads/ → 755
   uploads/brands/ → 755
   uploads/categories/ → 755
   uploads/products/ → 755
   uploads/certifications/ → 755
   uploads/slider/ → 755
   ```

**Via cPanel File Manager:**
- Right-click folder → Change Permissions → Set to 755

**Via FTP:**
- Right-click folder → File Permissions → 755

## Step 6: Configure .htaccess

The `.htaccess` file is already configured for shared hosting. However:

1. **If your site is in root domain:**
   - The current `.htaccess` should work as-is
   - `RewriteBase` is commented out (auto-detected)

2. **If your site is in a subdirectory:**
   - Edit `.htaccess`
   - Uncomment and set `RewriteBase`:
     ```
     RewriteBase /your-subdirectory/
     ```

3. **Verify mod_rewrite is enabled:**
   - Most shared hosting has this enabled by default
   - If URLs don't work, contact your host to enable it

## Step 7: Create Logs Directory (Optional)

1. Create `logs/` folder in root
2. Set permissions to `755`
3. Create `.htaccess` in `logs/` folder with:
   ```
   Deny from all
   ```

## Step 8: Test Your Installation

1. **Visit your domain:**
   - Should show the homepage
   - Test a few URLs: `/about-us`, `/contact-us`

2. **Test Admin Panel:**
   - Go to `https://yourdomain.com/admin/`
   - Login with default credentials:
     - Username: `admin`
     - Password: `admin123`
   - **IMPORTANT:** Change the admin password immediately!

3. **Test URL Routing:**
   - Visit a brand page (if you have brands)
   - Visit a category page
   - Visit a product page
   - All should work without `.php` extensions

## Step 9: Security Checklist

- [ ] Change admin password immediately
- [ ] Update `ADMIN_EMAIL` in `config/config.php`
- [ ] Verify `.htaccess` is protecting sensitive files
- [ ] Check that `uploads/` folder has proper permissions
- [ ] Ensure error display is OFF (already configured)

## Step 10: Enable HTTPS (SSL)

1. **Install SSL Certificate:**
   - Most shared hosts offer free SSL (Let's Encrypt)
   - In cPanel: SSL/TLS → Install SSL Certificate
   - Or use AutoSSL if available

2. **Force HTTPS (Optional):**
   Add to `.htaccess` before `RewriteEngine On`:
   ```apache
   RewriteCond %{HTTPS} off
   RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
   ```

## Troubleshooting

### URLs Return 404 Errors

1. **Check mod_rewrite:**
   - Contact hosting support to enable `mod_rewrite`
   - Verify `.htaccess` is in root directory

2. **Check RewriteBase:**
   - If in subdirectory, set `RewriteBase /subdirectory/`
   - If in root, leave commented or set to `/`

3. **Check file paths:**
   - Ensure `public/` folder exists
   - Verify all files uploaded correctly

### Database Connection Errors

1. **Verify credentials:**
   - Double-check database name, username, password
   - Some hosts require `username_dbname` format

2. **Check host:**
   - Usually `localhost`
   - Some hosts use `127.0.0.1` or a remote host

3. **Check PHP version:**
   - Ensure PHP 7.4+ is selected in cPanel
   - Check PHP extensions: `pdo_mysql`, `mysqli`

### Images Not Uploading

1. **Check permissions:**
   - `uploads/` folder must be `755` or `777`
   - All subfolders need write permissions

2. **Check PHP settings:**
   - `upload_max_filesize` should be at least 5MB
   - `post_max_size` should be larger than `upload_max_filesize`

3. **Check folder exists:**
   - Ensure all upload subfolders exist:
     - `uploads/brands/`
     - `uploads/categories/`
     - `uploads/products/`
     - `uploads/certifications/`
     - `uploads/slider/`

### White Screen / Blank Page

1. **Check error logs:**
   - cPanel → Error Log
   - Or check `logs/php_errors.log` if created

2. **Enable error display temporarily:**
   - Edit `config/config.php`
   - Change `ini_set('display_errors', 0);` to `1`
   - Fix errors, then change back

3. **Check PHP version:**
   - Ensure PHP 7.4+ is active

## Common Shared Hosting Paths

- **Root domain:** `public_html/`
- **Subdomain:** `public_html/subdomain/` or separate folder
- **Addon domain:** `public_html/addondomain/`
- **cPanel username:** Usually in path like `/home/username/`

## Post-Deployment

1. **Generate Sitemap:**
   - Login to admin panel
   - Go to Sitemap section
   - Click "Generate Sitemap"
   - Submit to Google Search Console

2. **Submit to Search Engines:**
   - Google Search Console
   - Bing Webmaster Tools

3. **Monitor:**
   - Check error logs regularly
   - Monitor uploads folder size
   - Keep backups of database and files

## Support

If you encounter issues:
1. Check error logs first
2. Verify all steps above
3. Contact your hosting provider for server-specific issues
4. Check PHP and Apache error logs in cPanel

---

**Note:** This configuration works on most shared hosting providers including:
- cPanel-based hosts (most common)
- Plesk-based hosts
- DirectAdmin hosts
- Most Apache-based shared hosting

