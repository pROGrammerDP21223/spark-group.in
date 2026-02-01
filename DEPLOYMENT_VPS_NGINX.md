# VPS Deployment Guide - Nginx

This guide will help you deploy this PHP website to a VPS server with Nginx, PHP-FPM, and MySQL.

## Prerequisites

- VPS with Ubuntu 20.04/22.04 or Debian 11/12 (recommended)
- Root or sudo access
- Domain name pointing to your VPS IP
- Basic knowledge of Linux commands

## Step 1: Initial Server Setup

### 1.1 Update System

```bash
sudo apt update
sudo apt upgrade -y
```

### 1.2 Install Required Software

```bash
# Install Nginx, PHP-FPM, MySQL, and required PHP extensions
sudo apt install -y nginx php-fpm php-mysql php-mbstring php-xml php-curl php-gd php-zip php-cli mysql-server

# For PHP 8.3 (recommended)
# Note: PHP 8.3 may require adding a PPA on older Ubuntu versions
# For Ubuntu 22.04+, PHP 8.3 is available. For Ubuntu 20.04, add PPA:
# sudo add-apt-repository ppa:ondrej/php -y
# sudo apt update
sudo apt install -y php8.3-fpm php8.3-mysql php8.3-mbstring php8.3-xml php8.3-curl php8.3-gd php8.3-zip php8.3-cli

# Or for PHP 8.2
sudo apt install -y php8.2-fpm php8.2-mysql php8.2-mbstring php8.2-xml php8.2-curl php8.2-gd php8.2-zip php8.2-cli

# Or for PHP 8.1
sudo apt install -y php8.1-fpm php8.1-mysql php8.1-mbstring php8.1-xml php8.1-curl php8.1-gd php8.1-zip php8.1-cli
```

### 1.3 Verify Installation

```bash
nginx -v
php -v
mysql --version
```

## Step 2: Configure MySQL Database

### 2.1 Secure MySQL Installation

```bash
sudo mysql_secure_installation
```

Follow the prompts:
- Set root password (or use auth_socket)
- Remove anonymous users: Yes
- Disallow root login remotely: Yes
- Remove test database: Yes
- Reload privilege tables: Yes

### 2.2 Create Database and User

```bash
sudo mysql -u root -p
```

In MySQL prompt:

```sql
CREATE DATABASE dealer_website CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'dealer_user'@'localhost' IDENTIFIED BY 'your_strong_password_here';
GRANT ALL PRIVILEGES ON dealer_website.* TO 'dealer_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

**Note:** Replace `dealer_website`, `dealer_user`, and `your_strong_password_here` with your values.

### 2.3 Import Database Schema

```bash
# Upload your database/schema.sql file to the server first
# Then import it:
mysql -u dealer_user -p dealer_website < /path/to/database/schema.sql
```

Or using MySQL prompt:

```bash
sudo mysql -u root -p
```

```sql
USE dealer_website;
SOURCE /path/to/database/schema.sql;
EXIT;
```

## Step 3: Upload Project Files

### 3.1 Create Web Directory

```bash
sudo mkdir -p /var/www/your-domain.com
sudo chown -R $USER:$USER /var/www/your-domain.com
```

### 3.2 Upload Files

**Option A: Using SCP (from your local machine)**

```bash
# From your local machine (Windows PowerShell or Linux/Mac terminal)
scp -r * user@your-server-ip:/var/www/your-domain.com/
```

**Option B: Using Git (if repository is on GitHub/GitLab)**

```bash
cd /var/www/your-domain.com
git clone https://github.com/your-username/your-repo.git .
```

**Option C: Using SFTP Client**

Use FileZilla, WinSCP, or similar to upload all files to `/var/www/your-domain.com/`

### 3.3 Set Proper Permissions

```bash
cd /var/www/your-domain.com

# Set ownership
sudo chown -R www-data:www-data /var/www/your-domain.com

# Set directory permissions
sudo find /var/www/your-domain.com -type d -exec chmod 755 {} \;

# Set file permissions
sudo find /var/www/your-domain.com -type f -exec chmod 644 {} \;

# Set write permissions for uploads
sudo chmod -R 755 uploads/
sudo chmod -R 755 config/
```

## Step 4: Configure PHP-FPM

### 4.1 Edit PHP-FPM Configuration

```bash
# For PHP 8.3 (recommended)
sudo nano /etc/php/8.3/fpm/php.ini

# For PHP 8.2
sudo nano /etc/php/8.2/fpm/php.ini

# For PHP 8.1
sudo nano /etc/php/8.1/fpm/php.ini
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
# For PHP 8.3 (recommended)
sudo systemctl restart php8.3-fpm

# For PHP 8.2
sudo systemctl restart php8.2-fpm

# For PHP 8.1
sudo systemctl restart php8.1-fpm
```

## Step 5: Configure Nginx

### 5.1 Copy Nginx Configuration

```bash
# Copy the nginx.conf from your project to nginx sites-available
sudo cp /var/www/your-domain.com/nginx.conf /etc/nginx/sites-available/your-domain.com
```

### 5.2 Edit Nginx Configuration

```bash
sudo nano /etc/nginx/sites-available/your-domain.com
```

**Important:** Update these values:
- `server_name`: Replace `your-domain.com` with your actual domain
- `root`: Replace `/var/www/your-domain.com` with your actual path
- `fastcgi_pass`: Update PHP version (e.g., `php8.3-fpm.sock` or `php8.2-fpm.sock`)

### 5.3 Enable Site

```bash
# Create symlink
sudo ln -s /etc/nginx/sites-available/your-domain.com /etc/nginx/sites-enabled/

# Remove default site (optional)
sudo rm /etc/nginx/sites-enabled/default

# Test nginx configuration
sudo nginx -t
```

### 5.4 Restart Nginx

```bash
sudo systemctl restart nginx
```

## Step 6: Configure Application

### 6.1 Update Database Configuration

```bash
sudo nano /var/www/your-domain.com/config/database.php
```

Update with your database credentials:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'dealer_website');  // Your database name
define('DB_USER', 'dealer_user');     // Your database user
define('DB_PASS', 'your_password');   // Your database password
define('DB_CHARSET', 'utf8mb4');
```

### 6.2 Update Site Configuration (Optional)

```bash
sudo nano /var/www/your-domain.com/config/config.php
```

If auto-detection doesn't work, uncomment and set:

```php
define('SITE_URL', 'https://your-domain.com');
```

## Step 7: Configure Firewall

### 7.1 Allow HTTP/HTTPS Traffic

```bash
# If using UFW
sudo ufw allow 'Nginx Full'
sudo ufw allow OpenSSH
sudo ufw enable

# If using firewalld (CentOS/RHEL)
sudo firewall-cmd --permanent --add-service=http
sudo firewall-cmd --permanent --add-service=https
sudo firewall-cmd --reload
```

## Step 8: Install SSL Certificate (Let's Encrypt)

### 8.1 Install Certbot

```bash
sudo apt install -y certbot python3-certbot-nginx
```

### 8.2 Obtain SSL Certificate

```bash
sudo certbot --nginx -d your-domain.com -d www.your-domain.com
```

Follow the prompts:
- Enter your email
- Agree to terms
- Choose whether to redirect HTTP to HTTPS (recommended: Yes)

### 8.3 Auto-Renewal

Certbot automatically sets up auto-renewal. Test it:

```bash
sudo certbot renew --dry-run
```

### 8.4 Update Nginx Config for HTTPS

After SSL installation, edit the nginx config to uncomment the HTTPS server block:

```bash
sudo nano /etc/nginx/sites-available/your-domain.com
```

Uncomment and configure the HTTPS server block, then:

```bash
sudo nginx -t
sudo systemctl reload nginx
```

## Step 9: Test Your Installation

### 9.1 Test Website

1. Visit `http://your-domain.com` (or `https://` if SSL is configured)
2. Check homepage loads correctly
3. Test admin panel: `http://your-domain.com/admin/`
   - Default credentials:
     - Username: `admin`
     - Password: `admin123`
   - **IMPORTANT:** Change password immediately!

### 9.2 Test URL Routing

- `/about-us`
- `/contact-us`
- `/bosch` (if you have brands)
- `/sitemap.xml`

### 9.3 Check Logs

```bash
# Nginx error logs
sudo tail -f /var/log/nginx/your-domain.com-error.log

# PHP-FPM error logs
sudo tail -f /var/log/php8.3-fpm.log  # or php8.2-fpm.log

# Application logs (if created)
tail -f /var/www/your-domain.com/logs/php_errors.log
```

## Step 10: Security Hardening

### 10.1 Create Logs Directory

```bash
sudo mkdir -p /var/www/your-domain.com/logs
sudo chmod 755 /var/www/your-domain.com/logs
sudo chown www-data:www-data /var/www/your-domain.com/logs
```

### 10.2 Disable Directory Listing

Already configured in nginx.conf, but verify:

```bash
# Ensure nginx config has:
# No directory listing (default behavior)
```

### 10.3 Update Admin Password

1. Login to admin panel
2. Go to admin settings (if available) or update directly in database:

```sql
UPDATE admins SET password = MD5('new_strong_password') WHERE username = 'admin';
```

### 10.4 Regular Backups

Create a backup script:

```bash
sudo nano /usr/local/bin/backup-website.sh
```

```bash
#!/bin/bash
BACKUP_DIR="/backups/website"
DATE=$(date +%Y%m%d_%H%M%S)
mkdir -p $BACKUP_DIR

# Backup database
mysqldump -u dealer_user -p'your_password' dealer_website > $BACKUP_DIR/db_$DATE.sql

# Backup files
tar -czf $BACKUP_DIR/files_$DATE.tar.gz /var/www/your-domain.com

# Keep only last 7 days
find $BACKUP_DIR -type f -mtime +7 -delete
```

Make executable:

```bash
sudo chmod +x /usr/local/bin/backup-website.sh
```

Add to crontab:

```bash
sudo crontab -e
```

Add:

```
0 2 * * * /usr/local/bin/backup-website.sh
```

## Troubleshooting

### Nginx 502 Bad Gateway

1. **Check PHP-FPM is running:**
   ```bash
   sudo systemctl status php8.3-fpm  # or php8.2-fpm
   ```

2. **Check PHP-FPM socket path:**
   ```bash
   ls -la /var/run/php/
   ```
   Update nginx config with correct socket path.

3. **Check PHP-FPM pool configuration:**
   ```bash
   sudo nano /etc/php/8.3/fpm/pool.d/www.conf
   ```
   Ensure `listen = /var/run/php/php8.3-fpm.sock`

### URLs Return 404

1. **Check nginx configuration:**
   ```bash
   sudo nginx -t
   ```

2. **Verify file paths in nginx config match actual paths**

3. **Check nginx error logs:**
   ```bash
   sudo tail -f /var/log/nginx/error.log
   ```

### Database Connection Errors

1. **Test database connection:**
   ```bash
   mysql -u dealer_user -p dealer_website
   ```

2. **Check database credentials in config/database.php**

3. **Verify MySQL is running:**
   ```bash
   sudo systemctl status mysql
   ```

### Images Not Uploading

1. **Check uploads folder permissions:**
   ```bash
   ls -la /var/www/your-domain.com/uploads/
   sudo chmod -R 755 /var/www/your-domain.com/uploads/
   sudo chown -R www-data:www-data /var/www/your-domain.com/uploads/
   ```

2. **Check PHP upload settings:**
   ```bash
   php -i | grep upload_max_filesize
   ```

### Permission Denied Errors

1. **Check file ownership:**
   ```bash
   sudo chown -R www-data:www-data /var/www/your-domain.com
   ```

2. **Check file permissions:**
   ```bash
   sudo find /var/www/your-domain.com -type d -exec chmod 755 {} \;
   sudo find /var/www/your-domain.com -type f -exec chmod 644 {} \;
   ```

## Maintenance Commands

```bash
# Restart services
sudo systemctl restart nginx
sudo systemctl restart php8.3-fpm  # or php8.2-fpm
sudo systemctl restart mysql

# Check service status
sudo systemctl status nginx
sudo systemctl status php8.3-fpm
sudo systemctl status mysql

# View logs
sudo tail -f /var/log/nginx/error.log
sudo tail -f /var/log/nginx/access.log
sudo tail -f /var/log/php8.3-fpm.log
```

## Post-Deployment Checklist

- [ ] Website loads correctly
- [ ] Admin panel accessible
- [ ] Admin password changed
- [ ] SSL certificate installed and working
- [ ] All URLs routing correctly
- [ ] File uploads working
- [ ] Database connection working
- [ ] Error logging configured
- [ ] Backups configured
- [ ] Firewall configured
- [ ] Sitemap generated and accessible

## Support

If you encounter issues:
1. Check error logs first
2. Verify all configuration files
3. Test database connection
4. Check file permissions
5. Review nginx and PHP-FPM status

---

**Note:** This configuration works on Ubuntu/Debian-based VPS servers. Adjust paths and commands for other distributions (CentOS, RHEL, etc.).

