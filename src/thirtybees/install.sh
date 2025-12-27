#!/bin/bash
# Installation script for Renewed Renaissance - Art & Recycled Art Supplies Shop

set -e

# Wait for MariaDB to be ready
echo "Waiting for MariaDB to be ready..."
max_attempts=30
attempt=0
while ! mariadb -h"${DB_SERVER:-mariadb}" -u"${DB_USER:-thirtybees}" -p"${DB_PASSWORD:-thirtybees}" -e "SELECT 1" > /dev/null 2>&1; do
    attempt=$((attempt + 1))
    if [ $attempt -ge $max_attempts ]; then
        echo "Error: MariaDB is not available after $max_attempts attempts"
        exit 1
    fi
    echo "MariaDB is unavailable - sleeping (attempt $attempt/$max_attempts)"
    sleep 2
done
echo "MariaDB is ready!"

# Check if already installed
if [ -f "/var/www/default/config/settings.inc.php" ]; then
    echo "thirty bees is already installed. Skipping installation."
    exit 0
fi

echo "Starting thirty bees installation for Renewed Renaissance..."

cd /var/www/default

# Run CLI installer
php install-dev/index_cli.php \
    --step=all \
    --language=en \
    --timezone=America/New_York \
    --base_uri=/ \
    --domain="${SHOP_DOMAIN:-thirtybees-dev.renewed-renaissance.com}" \
    --db_server="${DB_SERVER:-mariadb}" \
    --db_user="${DB_USER:-thirtybees}" \
    --db_password="${DB_PASSWORD:-thirtybees}" \
    --db_name="${DB_NAME:-thirtybees}" \
    --db_clear=1 \
    --db_create=1 \
    --prefix=tb_ \
    --name="Renewed Renaissance" \
    --activity=3 \
    --country=us \
    --firstname="${ADMIN_FIRSTNAME:-Admin}" \
    --lastname="${ADMIN_LASTNAME:-User}" \
    --password="${ADMIN_PASSWORD:-RenewedRenaissance2025!}" \
    --email="${ADMIN_EMAIL:-admin@renewed-renaissance.com}"

echo "Installation completed successfully!"

# Set proper permissions
chown -R www-data:www-data /var/www/default/config
chown -R www-data:www-data /var/www/default/cache
chown -R www-data:www-data /var/www/default/img
chown -R www-data:www-data /var/www/default/log
chown -R www-data:www-data /var/www/default/mails
chown -R www-data:www-data /var/www/default/modules
chown -R www-data:www-data /var/www/default/themes
chown -R www-data:www-data /var/www/default/translations
chown -R www-data:www-data /var/www/default/upload
chown -R www-data:www-data /var/www/default/download

echo "Permissions set successfully!"
