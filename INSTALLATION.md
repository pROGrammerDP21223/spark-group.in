# Installation Guide

## Quick Start

### Step 1: Database Setup

1. Open phpMyAdmin or MySQL command line
2. Create a new database (or use existing):
   ```sql
   CREATE DATABASE dealer_website;
   ```
3. Import the schema:
   - Via phpMyAdmin: Import `database/schema.sql`
   - Via command line: `mysql -u root -p dealer_website < database/schema.sql`

### Step 2: Configuration

1. Edit `config/database.php`:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'dealer_website');
   define('DB_USER', 'root');
   define('DB_PASS', ''); // Your MySQL password
   ```

2. Edit `config/config.php`:
   ```php
   define('SITE_URL', 'http://localhost/sparks'); // Update to your URL
   ```

### Step 3: File Permissions

Create upload directories and set permissions:
```bash
mkdir -p uploads/brands
mkdir -p uploads/categories
mkdir -p uploads/products
mkdir -p uploads/products/gallery
mkdir -p uploads/certifications
mkdir -p uploads/testimonials

chmod -R 755 uploads/
```

### Step 4: Apache Configuration

Ensure mod_rewrite is enabled:
```bash
# Check if enabled
apache2ctl -M | grep rewrite

# If not enabled, enable it:
sudo a2enmod rewrite
sudo service apache2 restart
```

In your Apache virtual host or `.htaccess`, ensure:
```
AllowOverride All
```

### Step 5: Access the Website

1. **Public Website**: `http://localhost/sparks/`
2. **Admin Panel**: `http://localhost/sparks/admin/`
   - Username: `admin`
   - Password: `admin123`

### Step 6: Initial Setup

1. Login to admin panel
2. Add your first brand
3. Add cities (e.g., Pune, Mumbai)
4. Add categories for the brand
5. Add products
6. Configure SEO for important pages
7. Generate sitemap: Admin → Sitemap

## Troubleshooting

### Images Not Uploading
- Check PHP `upload_max_filesize` and `post_max_size` in `php.ini`
- Ensure `uploads/` directory is writable (755 or 777)
- Check PHP error logs

### URLs Not Working (404 Errors)
- Ensure mod_rewrite is enabled
- Check `.htaccess` file exists
- Verify `AllowOverride All` in Apache config
- Check Apache error logs

### Database Connection Error
- Verify database credentials in `config/database.php`
- Ensure MySQL service is running
- Check database name exists

### Admin Login Not Working
- Default password is hashed. If changed, use:
  ```php
  echo password_hash('yourpassword', PASSWORD_DEFAULT);
  ```
  Then update in database.

## Next Steps

1. **Add Content**:
   - Add brands
   - Add cities
   - Add categories and products
   - Add certifications and testimonials

2. **Configure SEO**:
   - Set meta tags for all pages
   - Configure city-specific SEO
   - Submit sitemap to Google Search Console

3. **Customize**:
   - Update site name in `config/config.php`
   - Customize colors in `public/includes/header.php`
   - Add your logo and branding

4. **Security**:
   - Change default admin password
   - Update `config/config.php` error reporting to 0 in production
   - Set proper file permissions

## Production Deployment

1. Set `error_reporting(0)` in `config/config.php`
2. Use strong admin passwords
3. Enable HTTPS
4. Set proper file permissions (755 for directories, 644 for files)
5. Regular database backups
6. Update `SITE_URL` to production domain

