#!/bin/bash

# ====================================
# VPS Deployment Script
# Professional Dealer Website - Nginx Setup
# ====================================
#
# Usage: sudo bash deploy-vps.sh
#
# This script helps automate the deployment process
# Make sure to review and customize before running
#

set -e  # Exit on error

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Configuration Variables (CUSTOMIZE THESE)
DOMAIN="spark-group.in"
WEB_ROOT="/var/www/${DOMAIN}"
DB_NAME="dealer_website"
DB_USER="dealer_user"
DB_PASS=""  # Will prompt if empty
PHP_VERSION="8.3"  # or 8.2, 8.1

echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}VPS Deployment Script${NC}"
echo -e "${GREEN}========================================${NC}"
echo ""

# Check if running as root
if [ "$EUID" -ne 0 ]; then 
    echo -e "${RED}Please run as root or with sudo${NC}"
    exit 1
fi

# Get database password if not set
if [ -z "$DB_PASS" ]; then
    read -sp "Enter database password for $DB_USER: " DB_PASS
    echo ""
fi

# Step 1: Update System
echo -e "${YELLOW}[1/10] Updating system packages...${NC}"
apt update && apt upgrade -y

# Step 2: Install Required Software
echo -e "${YELLOW}[2/10] Installing Nginx, PHP-FPM, MySQL...${NC}"
# For PHP 8.3, may need to add PPA on older Ubuntu versions
if [ "$PHP_VERSION" = "8.3" ]; then
    if ! apt-cache show php8.3-fpm &>/dev/null; then
        echo -e "${YELLOW}Adding PHP PPA for PHP 8.3...${NC}"
        add-apt-repository ppa:ondrej/php -y
        apt update
    fi
fi
apt install -y nginx php${PHP_VERSION}-fpm php${PHP_VERSION}-mysql php${PHP_VERSION}-mbstring \
    php${PHP_VERSION}-xml php${PHP_VERSION}-curl php${PHP_VERSION}-gd php${PHP_VERSION}-zip \
    php${PHP_VERSION}-cli mysql-server

# Step 3: Configure MySQL
echo -e "${YELLOW}[3/10] Configuring MySQL database...${NC}"
if [ ! -d "/var/lib/mysql/${DB_NAME}" ]; then
    mysql -u root <<EOF
CREATE DATABASE IF NOT EXISTS ${DB_NAME} CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASS}';
GRANT ALL PRIVILEGES ON ${DB_NAME}.* TO '${DB_USER}'@'localhost';
FLUSH PRIVILEGES;
EOF
    echo -e "${GREEN}Database created successfully${NC}"
else
    echo -e "${YELLOW}Database already exists, skipping...${NC}"
fi

# Step 4: Create Web Directory
echo -e "${YELLOW}[4/10] Creating web directory...${NC}"
mkdir -p ${WEB_ROOT}
chown -R www-data:www-data ${WEB_ROOT}

# Step 5: Configure PHP-FPM
echo -e "${YELLOW}[5/10] Configuring PHP-FPM...${NC}"
PHP_INI="/etc/php/${PHP_VERSION}/fpm/php.ini"
sed -i 's/upload_max_filesize = .*/upload_max_filesize = 10M/' ${PHP_INI}
sed -i 's/post_max_size = .*/post_max_size = 10M/' ${PHP_INI}
sed -i 's/memory_limit = .*/memory_limit = 256M/' ${PHP_INI}
sed -i 's/;date.timezone =.*/date.timezone = Asia\/Kolkata/' ${PHP_INI}

systemctl restart php${PHP_VERSION}-fpm
echo -e "${GREEN}PHP-FPM configured and restarted${NC}"

# Step 6: Configure Nginx
echo -e "${YELLOW}[6/10] Configuring Nginx...${NC}"

# Check if nginx config exists in project
if [ -f "${WEB_ROOT}/nginx.conf" ]; then
    # Copy and customize nginx config
    cp ${WEB_ROOT}/nginx.conf /etc/nginx/sites-available/${DOMAIN}
    
    # Replace placeholders
    sed -i "s/spark-group.in/${DOMAIN}/g" /etc/nginx/sites-available/${DOMAIN}
    sed -i "s|/var/www/spark-group.in|${WEB_ROOT}|g" /etc/nginx/sites-available/${DOMAIN}
    sed -i "s/php8.1-fpm/php${PHP_VERSION}-fpm/g" /etc/nginx/sites-available/${DOMAIN}
    sed -i "s/php8.2-fpm/php${PHP_VERSION}-fpm/g" /etc/nginx/sites-available/${DOMAIN}
else
    echo -e "${RED}nginx.conf not found in ${WEB_ROOT}${NC}"
    echo -e "${YELLOW}Please create nginx configuration manually${NC}"
fi

# Enable site
if [ ! -L "/etc/nginx/sites-enabled/${DOMAIN}" ]; then
    ln -s /etc/nginx/sites-available/${DOMAIN} /etc/nginx/sites-enabled/
fi

# Remove default site
if [ -L "/etc/nginx/sites-enabled/default" ]; then
    rm /etc/nginx/sites-enabled/default
fi

# Test nginx configuration
if nginx -t; then
    systemctl restart nginx
    echo -e "${GREEN}Nginx configured and restarted${NC}"
else
    echo -e "${RED}Nginx configuration test failed!${NC}"
    exit 1
fi

# Step 7: Set File Permissions
echo -e "${YELLOW}[7/10] Setting file permissions...${NC}"
if [ -d "${WEB_ROOT}" ]; then
    find ${WEB_ROOT} -type d -exec chmod 755 {} \;
    find ${WEB_ROOT} -type f -exec chmod 644 {} \;
    
    # Special permissions for uploads and config
    if [ -d "${WEB_ROOT}/uploads" ]; then
        chmod -R 755 ${WEB_ROOT}/uploads
    fi
    
    if [ -d "${WEB_ROOT}/config" ]; then
        chmod 755 ${WEB_ROOT}/config
    fi
    
    # Create logs directory
    mkdir -p ${WEB_ROOT}/logs
    chmod 755 ${WEB_ROOT}/logs
    chown www-data:www-data ${WEB_ROOT}/logs
    
    echo -e "${GREEN}File permissions set${NC}"
else
    echo -e "${YELLOW}Web root directory not found, skipping permissions${NC}"
fi

# Step 8: Configure Firewall
echo -e "${YELLOW}[8/10] Configuring firewall...${NC}"
if command -v ufw &> /dev/null; then
    ufw allow 'Nginx Full'
    ufw allow OpenSSH
    echo -e "${GREEN}Firewall configured${NC}"
else
    echo -e "${YELLOW}UFW not installed, skipping firewall configuration${NC}"
fi

# Step 9: Update Database Configuration
echo -e "${YELLOW}[9/10] Updating database configuration...${NC}"
if [ -f "${WEB_ROOT}/config/database.php" ]; then
    # Backup original
    cp ${WEB_ROOT}/config/database.php ${WEB_ROOT}/config/database.php.bak
    
    # Update database credentials
    sed -i "s/define('DB_NAME', '[^']*');/define('DB_NAME', '${DB_NAME}');/" ${WEB_ROOT}/config/database.php
    sed -i "s/define('DB_USER', '[^']*');/define('DB_USER', '${DB_USER}');/" ${WEB_ROOT}/config/database.php
    sed -i "s/define('DB_PASS', '[^']*');/define('DB_PASS', '${DB_PASS}');/" ${WEB_ROOT}/config/database.php
    
    echo -e "${GREEN}Database configuration updated${NC}"
else
    echo -e "${YELLOW}config/database.php not found, please update manually${NC}"
fi

# Step 10: Summary
echo ""
echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}Deployment Summary${NC}"
echo -e "${GREEN}========================================${NC}"
echo ""
echo -e "Domain: ${DOMAIN}"
echo -e "Web Root: ${WEB_ROOT}"
echo -e "Database: ${DB_NAME}"
echo -e "Database User: ${DB_USER}"
echo -e "PHP Version: ${PHP_VERSION}"
echo ""
echo -e "${YELLOW}Next Steps:${NC}"
echo ""
echo "1. Upload your project files to: ${WEB_ROOT}"
echo "2. Import database schema:"
echo "   mysql -u ${DB_USER} -p ${DB_NAME} < database/schema.sql"
echo "3. Update config/config.php with your SITE_URL if needed"
echo "4. Test your website: http://${DOMAIN}"
echo "5. Install SSL certificate:"
echo "   sudo apt install certbot python3-certbot-nginx"
echo "   sudo certbot --nginx -d ${DOMAIN} -d www.${DOMAIN}"
echo ""
echo -e "${GREEN}Deployment script completed!${NC}"
echo ""

