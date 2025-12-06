#!/bin/bash

# Wait for MySQL to be ready
echo "Waiting for database connection..."
echo "DB Connection Info:"
php -r "echo 'Host: ' . (getenv('DB_HOST') ?: 'db') . PHP_EOL;"
php -r "echo 'Port: ' . (getenv('DB_PORT') ?: '3306') . PHP_EOL;"
php -r "echo 'Database: ' . (getenv('DB_DATABASE') ?: 'badminton_shop') . PHP_EOL;"

max_tries=30
count=0
while ! php -r "
    \$host = getenv('DB_HOST') ?: 'db';
    \$port = getenv('DB_PORT') ?: '3306';
    \$db = getenv('DB_DATABASE') ?: 'badminton_shop';
    \$user = getenv('DB_USERNAME') ?: 'root';
    \$pass = getenv('DB_PASSWORD') ?: 'root';
    \$options = [
        PDO::MYSQL_ATTR_SSL_CA => '/etc/ssl/certs/ca-certificates.crt',
        PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false,
    ];
    try { 
        new PDO(\"mysql:host=\$host;port=\$port;dbname=\$db\", \$user, \$pass, \$options); 
    } catch (PDOException \$e) { 
        echo \$e->getMessage() . PHP_EOL;
        exit(1); 
    }
" > /dev/null 2>&1; do
    echo "Database is unavailable - sleeping"
    sleep 2
    count=$((count+1))
    if [ $count -ge $max_tries ]; then
        echo "Error: Database connection timed out."
        exit 1
    fi
done
echo "Database is up!"

if [ ! -f .env ]; then
    echo "Creating .env file..."
    cp .env.example .env
fi

# Override .env with environment variables from Render
echo "==> Updating .env with environment variables..."
[ ! -z "$APP_KEY" ] && sed -i "s|^APP_KEY=.*|APP_KEY=$APP_KEY|" .env || echo "APP_KEY=$APP_KEY" >> .env
[ ! -z "$APP_URL" ] && sed -i "s|^APP_URL=.*|APP_URL=$APP_URL|" .env || echo "APP_URL=$APP_URL" >> .env
[ ! -z "$APP_ENV" ] && sed -i "s|^APP_ENV=.*|APP_ENV=$APP_ENV|" .env || echo "APP_ENV=$APP_ENV" >> .env
[ ! -z "$APP_DEBUG" ] && sed -i "s|^APP_DEBUG=.*|APP_DEBUG=$APP_DEBUG|" .env || echo "APP_DEBUG=$APP_DEBUG" >> .env

# Session configuration
[ ! -z "$SESSION_DRIVER" ] && sed -i "s|^SESSION_DRIVER=.*|SESSION_DRIVER=$SESSION_DRIVER|" .env || echo "SESSION_DRIVER=$SESSION_DRIVER" >> .env
[ ! -z "$SESSION_LIFETIME" ] && sed -i "s|^SESSION_LIFETIME=.*|SESSION_LIFETIME=$SESSION_LIFETIME|" .env || echo "SESSION_LIFETIME=$SESSION_LIFETIME" >> .env

# Remove SESSION_DOMAIN entirely so Laravel uses current domain (null by default)
sed -i "/^SESSION_DOMAIN=/d" .env

# Let Laravel auto-detect secure cookies based on request scheme (don't force true)
echo "==> DEBUG: SESSION_SECURE_COOKIE left as null for auto-detection"

# Keep SameSite as lax (already in .env.example)
echo "==> DEBUG: SESSION_SAME_SITE should be lax from .env.example"

# Cache
[ ! -z "$CACHE_DRIVER" ] && sed -i "s|^CACHE_DRIVER=.*|CACHE_DRIVER=$CACHE_DRIVER|" .env || echo "CACHE_DRIVER=$CACHE_DRIVER" >> .env

echo "==> Environment variables applied to .env"
echo "==> DEBUG: Full .env content related to SESSION:"
grep "^SESSION" .env || echo "No SESSION variables found!"
echo "==> Checking critical .env values:"
echo "APP_KEY=$(grep '^APP_KEY=' .env | cut -d '=' -f 2 | head -c 20)..."
echo "APP_URL=$(grep '^APP_URL=' .env | cut -d '=' -f 2)"
echo "SESSION_DRIVER=$(grep '^SESSION_DRIVER=' .env | cut -d '=' -f 2)"
echo "SESSION_DOMAIN=$(grep '^SESSION_DOMAIN=' .env | cut -d '=' -f 2)"
echo "SESSION_SECURE_COOKIE=$(grep '^SESSION_SECURE_COOKIE=' .env | cut -d '=' -f 2)"
echo "CACHE_DRIVER=$(grep '^CACHE_DRIVER=' .env | cut -d '=' -f 2)"

# CRITICAL: Remove all Laravel cache files before clearing cache
echo "==> Removing Laravel cache files..."
rm -f bootstrap/cache/config.php
rm -f bootstrap/cache/routes.php
rm -f bootstrap/cache/services.php
rm -rf storage/framework/cache/data/*
rm -rf storage/framework/views/*

# IMPORTANT: Clear Laravel config cache AFTER updating .env
echo "==> Clearing Laravel config cache to pick up new .env values..."
php artisan config:clear
php artisan cache:clear

if [ -z "$(grep '^APP_KEY=' .env | cut -d '=' -f 2)" ]; then
    echo "Generating application key..."
    php artisan key:generate
fi

# Run migrations
echo "Running migrations..."
php artisan migrate --force

# Run seeders only if admin user doesn't exist
echo "Checking if seeding is needed..."
USER_COUNT=$(php -r "
try {
    \$host = getenv('DB_HOST') ?: 'db';
    \$port = getenv('DB_PORT') ?: '3306';
    \$db = getenv('DB_DATABASE') ?: 'badminton_shop';
    \$user = getenv('DB_USERNAME') ?: 'root';
    \$pass = getenv('DB_PASSWORD') ?: 'root';
    \$options = [
        PDO::MYSQL_ATTR_SSL_CA => '/etc/ssl/certs/ca-certificates.crt',
        PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false,
    ];
    \$pdo = new PDO(\"mysql:host=\$host;port=\$port;dbname=\$db\", \$user, \$pass, \$options);
    \$stmt = \$pdo->query(\"SELECT count(*) FROM users WHERE email = 'admin@badmintonshop.com'\");
    echo \$stmt->fetchColumn();
} catch (Exception \$e) {
    echo 0;
}
")

if [ "$USER_COUNT" -eq "0" ]; then
    echo "Seeding database..."
    php artisan db:seed --force
else
    echo "Database already seeded."
fi

# Check and fix session storage permissions
echo "==> Checking session storage..."
SESSION_PATH="storage/framework/sessions"
if [ ! -d "$SESSION_PATH" ]; then
    echo "Creating session directory: $SESSION_PATH"
    mkdir -p "$SESSION_PATH"
fi
chmod -R 775 storage/framework/sessions
chown -R www-data:www-data storage/framework/sessions
echo "Session path: $SESSION_PATH"
echo "Session permissions: $(ls -ld storage/framework/sessions)"

echo "==> DEBUG: About to configure Apache ports..."
echo "==> Current ports.conf content:"
cat /etc/apache2/ports.conf || echo "ERROR: Cannot read ports.conf"

# Get PORT from environment (Render uses PORT variable, default to 80)
APP_PORT=${PORT:-80}
echo "==> Configuring Apache to listen on 0.0.0.0:$APP_PORT..."

# Fix Apache to listen on 0.0.0.0 with PORT from environment
cat > /etc/apache2/ports.conf << EOF
# Listen on all interfaces - required for Render/external access
Listen 0.0.0.0:${APP_PORT}

<IfModule ssl_module>
    Listen 443
</IfModule>

<IfModule mod_gnutls.c>
    Listen 443
</IfModule>
EOF

# Update VirtualHost to listen on the correct port
sed -i "s/<VirtualHost \*:80>/<VirtualHost *:${APP_PORT}>/" /etc/apache2/sites-available/000-default.conf

echo "==> NEW Ports configuration:"
cat /etc/apache2/ports.conf

echo "==> VirtualHost configuration:"
grep "VirtualHost" /etc/apache2/sites-available/000-default.conf

echo "==> Testing if Apache will accept the config..."
apache2ctl configtest || echo "WARNING: Apache config test failed"

# Final cache clear before starting Apache
echo "==> Final cache clear..."
php artisan optimize:clear
php artisan route:clear
php artisan view:clear

echo "Starting Apache..."
exec apache2-foreground
