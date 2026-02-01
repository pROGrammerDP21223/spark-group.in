# Quick VPS Deployment Guide

## Quick Start (5 Steps)

### 1. Upload Files to VPS

```bash
# From your local machine
scp -r * user@your-server-ip:/var/www/spark-group.in/
```

Or use SFTP client (FileZilla, WinSCP, etc.)

### 2. Run Deployment Script (Optional but Recommended)

```bash
# SSH into your VPS
ssh user@your-server-ip

# Make script executable
chmod +x /var/www/spark-group.in/deploy-vps.sh

# Edit script variables first (DOMAIN, DB_NAME, etc.)
nano /var/www/spark-group.in/deploy-vps.sh

# Run script
sudo bash /var/www/spark-group.in/deploy-vps.sh
```

### 3. Manual Setup (If not using script)

```bash
# Install software
sudo apt update
# Note: For Ubuntu 20.04, you may need to add PHP PPA first:
# sudo add-apt-repository ppa:ondrej/php -y && sudo apt update
sudo apt install -y nginx php8.3-fpm php8.3-mysql php8.3-mbstring php8.3-xml php8.3-curl php8.3-gd mysql-server

# Create database
sudo mysql -u root -p
# Then run:
# CREATE DATABASE dealer_website;
# CREATE USER 'dealer_user'@'localhost' IDENTIFIED BY 'password';
# GRANT ALL PRIVILEGES ON dealer_website.* TO 'dealer_user'@'localhost';
# FLUSH PRIVILEGES;
# EXIT;

# Import database
mysql -u dealer_user -p dealer_website < database/schema.sql

# Configure nginx
sudo cp nginx.conf /etc/nginx/sites-available/spark-group.in
sudo nano /etc/nginx/sites-available/spark-group.in  # Edit domain and paths
sudo ln -s /etc/nginx/sites-available/spark-group.in /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx

# Set permissions
sudo chown -R www-data:www-data /var/www/spark-group.in
sudo chmod -R 755 /var/www/spark-group.in/uploads
```

### 4. Update Configuration

Edit `/var/www/spark-group.in/config/database.php`:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'dealer_website');
define('DB_USER', 'dealer_user');
define('DB_PASS', 'your_password');
```

### 5. Install SSL (Optional but Recommended)

```bash
sudo apt install -y certbot python3-certbot-nginx
sudo certbot --nginx -d spark-group.in -d www.spark-group.in
```

## Important Files

- **nginx.conf** - Nginx server configuration
- **DEPLOYMENT_VPS_NGINX.md** - Complete detailed guide
- **deploy-vps.sh** - Automated deployment script

## Default Admin Credentials

- URL: `http://spark-group.in/admin/`
- Username: `admin`
- Password: `admin123`

**⚠️ CHANGE THIS IMMEDIATELY AFTER FIRST LOGIN!**

## Common Commands

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
sudo tail -f /var/log/nginx/spark-group.in-error.log
```

## Troubleshooting

**502 Bad Gateway:**
- Check PHP-FPM is running: `sudo systemctl status php8.3-fpm`
- Verify socket path in nginx config matches PHP version

**404 Errors:**
- Check nginx config: `sudo nginx -t`
- Verify file paths are correct

**Database Connection:**
- Test: `mysql -u dealer_user -p dealer_website`
- Check credentials in `config/database.php`

For detailed troubleshooting, see **DEPLOYMENT_VPS_NGINX.md**

