# VPS Deployment Guide - Nginx & PHP 8.3

Complete guide to deploy this PHP website to a VPS server with Nginx and PHP 8.3.

## Prerequisites

- VPS with Ubuntu 20.04/22.04 or Debian 11/12
- Root or sudo access
- Domain name pointing to your VPS IP
- Basic Linux knowledge

## Step 1: Server Setup

### 1.1 Update System

```bash
sudo apt update
sudo apt upgrade -y
```

### 1.2 Install Required Software

```bash
# Install Nginx, PHP 8.3, MySQL, and required extensions
sudo apt install -y nginx mysql-server

# For PHP 8.3 (may need PPA on Ubuntu 20.04)
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update
sudo apt install -y php8.3-fpm php8.3-mysql php8.3-mbstring php8.3-xml php8.3-curl php8.3-gd php8.3-zip php8.3-cli
```

### 1.3 Verify Installation

```bash
nginx -v
php -v
mysql --version
```

## Step 2: Database Setup

### 2.1 Secure MySQL

```bash
sudo mysql_secure_installation
```

### 2.2 Create Database and User

```bash
sudo mysql -u root -p
```

In MySQL prompt:

```sql
CREATE DATABASE dealer_website CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'dealer_user'@'localhost' IDENTIFIED BY 'your_strong_password';
GRANT ALL PRIVILEGES ON dealer_website.* TO 'dealer_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### 2.3 Import Database Schema

```bash
mysql -u dealer_user -p dealer_website < database/schema.sql
```

## Step 3: Upload Project Files

### 3.1 Create Web Directory

```bash
sudo mkdir -p /var/www/spark-group.in
sudo chown -R $USER:$USER /var/www/spark-group.in
```

### 3.2 Upload Files

**Option A: Using Git**

```bash
cd /var/www/spark-group.in
git clone https://github.com/pROGrammerDP21223/spark-group.in.git .
```

**Option B: Using SCP (from local machine)**

```bash
scp -r * user@your-server-ip:/var/www/spark-group.in/
```

**Option C: Using SFTP Client**

Use FileZilla, WinSCP, etc. to upload all files.

### 3.3 Set Permissions

```bash
cd /var/www/spark-group.in

# Set ownership
sudo chown -R www-data:www-data /var/www/spark-group.in

# Set directory permissions
sudo find /var/www/spark-group.in -type d -exec chmod 755 {} \;

# Set file permissions
sudo find /var/www/spark-group.in -type f -exec chmod 644 {} \;

# Set write permissions for uploads
sudo chmod -R 755 uploads/
```

## Step 4: Configure PHP-FPM

### 4.1 Edit PHP Configuration

```bash
sudo nano /etc/php/8.3/fpm/php.ini
```

Update these settings:

```ini
upload_max_filesize = 10M
post_max_size = 10M
memory_limit = 256M
max_execution_time = 300
date.timezone = Asia/Kolkata
```

### 4.2 Restart PHP-FPM

```bash
sudo systemctl restart php8.3-fpm
```

## Step 5: Configure Nginx

### 5.1 Copy Nginx Configuration

```bash
sudo cp /var/www/spark-group.in/nginx.conf /etc/nginx/sites-available/spark-group.in
```

### 5.2 Edit Nginx Configuration

```bash
sudo nano /etc/nginx/sites-available/spark-group.in
```

**Update these values:**
- `server_name`: Replace `your-domain.com` with `spark-group.in`
- `root`: Replace `/var/www/your-domain.com` with `/var/www/spark-group.in`
- `fastcgi_pass`: Verify PHP version (should be `php8.3-fpm.sock`)

### 5.3 Enable Site

```bash
# Create symlink
sudo ln -s /etc/nginx/sites-available/spark-group.in /etc/nginx/sites-enabled/

# Remove default site (optional)
sudo rm /etc/nginx/sites-enabled/default

# Test configuration
sudo nginx -t
```

### 5.4 Restart Nginx

```bash
sudo systemctl restart nginx
```

## Step 6: Configure Application

### 6.1 Update Database Configuration

```bash
sudo nano /var/www/spark-group.in/config/database.php
```

Update with your database credentials:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'dealer_website');
define('DB_USER', 'dealer_user');
define('DB_PASS', 'your_password');
define('DB_CHARSET', 'utf8mb4');
```

### 6.2 Update Site URL (Optional)

```bash
sudo nano /var/www/spark-group.in/config/config.php
```

If auto-detection doesn't work, uncomment and set:

```php
define('SITE_URL', 'https://spark-group.in');
```

## Step 7: Configure Firewall

```bash
# Allow HTTP/HTTPS
sudo ufw allow 'Nginx Full'
sudo ufw allow OpenSSH
sudo ufw enable
```

## Step 8: Install SSL Certificate

### 8.1 Install Certbot

```bash
sudo apt install -y certbot python3-certbot-nginx
```

### 8.2 Obtain SSL Certificate

```bash
sudo certbot --nginx -d spark-group.in -d www.spark-group.in
```

Follow prompts and choose to redirect HTTP to HTTPS.

### 8.3 Auto-Renewal

Certbot sets up auto-renewal automatically. Test it:

```bash
sudo certbot renew --dry-run
```

## Step 9: Test Installation

1. **Visit your website:** `https://spark-group.in`
2. **Test admin panel:** `https://spark-group.in/admin/`
   - Username: `admin`
   - Password: `admin123`
   - **Change password immediately!**
3. **Test URLs:**
   - `/about-us`
   - `/contact-us`
   - `/sitemap.xml`

## Step 10: Security Checklist

- [ ] Admin password changed
- [ ] SSL certificate installed
- [ ] Database credentials updated
- [ ] File permissions set correctly
- [ ] Sensitive directories protected (config/, database/, includes/)
- [ ] Error logging enabled
- [ ] Regular backups configured

## Troubleshooting

### Nginx 502 Bad Gateway

```bash
# Check PHP-FPM status
sudo systemctl status php8.3-fpm

# Check socket path
ls -la /var/run/php/

# Verify nginx config
sudo nginx -t
```

### URLs Return 404

```bash
# Check nginx error logs
sudo tail -f /var/log/nginx/error.log

# Verify file paths in nginx config
sudo nginx -t
```

### Database Connection Error

```bash
# Test database connection
mysql -u dealer_user -p dealer_website

# Check MySQL status
sudo systemctl status mysql
```

### Images Not Uploading

```bash
# Check uploads folder permissions
sudo chmod -R 755 /var/www/spark-group.in/uploads/
sudo chown -R www-data:www-data /var/www/spark-group.in/uploads/
```

## Maintenance Commands

```bash
# Restart services
sudo systemctl restart nginx
sudo systemctl restart php8.3-fpm
sudo systemctl restart mysql

# Check status
sudo systemctl status nginx
sudo systemctl status php8.3-fpm

# View logs
sudo tail -f /var/log/nginx/error.log
sudo tail -f /var/log/nginx/access.log
sudo tail -f /var/log/php8.3-fpm.log
```

## Project Structure

```
spark-group.in/
├── admin/              # Admin dashboard
├── api/                # API endpoints
├── assets/             # CSS, JS, images, fonts
├── config/             # Configuration files (protected)
├── database/           # SQL files (protected)
├── includes/           # PHP includes (protected)
├── uploads/            # Uploaded files
├── index.php           # Main router (handles all routes)
├── home.php            # Home page
├── about.php           # About page
├── contact.php         # Contact page
└── nginx.conf          # Nginx configuration
```

## Security Features

The nginx configuration protects sensitive directories:
- `/config/` - Database credentials (BLOCKED)
- `/database/` - SQL files (BLOCKED)
- `/includes/` - PHP includes (BLOCKED)
- `/logs/` - Log files (BLOCKED)

These directories cannot be accessed directly via web browser.

## Support

For issues:
1. Check error logs first
2. Verify all configuration files
3. Test database connection
4. Check file permissions
5. Review nginx and PHP-FPM status

---

**Note:** This configuration works on Ubuntu/Debian-based VPS servers with PHP 8.3 and Nginx.

