FROM php:8.2-apache

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libzip-dev \
    ca-certificates

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# Fix Apache to listen on 0.0.0.0 instead of ::1
# Replace the default ports.conf to listen on all interfaces
RUN echo "# Listen on all interfaces" > /etc/apache2/ports.conf && \
    echo "Listen 0.0.0.0:80" >> /etc/apache2/ports.conf && \
    echo "" >> /etc/apache2/ports.conf && \
    echo "<IfModule ssl_module>" >> /etc/apache2/ports.conf && \
    echo "    Listen 443" >> /etc/apache2/ports.conf && \
    echo "</IfModule>" >> /etc/apache2/ports.conf && \
    echo "" >> /etc/apache2/ports.conf && \
    echo "<IfModule mod_gnutls.c>" >> /etc/apache2/ports.conf && \
    echo "    Listen 443" >> /etc/apache2/ports.conf && \
    echo "</IfModule>" >> /etc/apache2/ports.conf

# Set ServerName globally
RUN echo "ServerName 0.0.0.0" >> /etc/apache2/apache2.conf

# Configure Apache DocumentRoot to point to Laravel's public directory
COPY docker/apache/000-default.conf /etc/apache2/sites-available/000-default.conf

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy existing application directory contents
COPY . /var/www/html

# Install dependencies
RUN composer install --no-interaction --optimize-autoloader --no-dev

# Set permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 /var/www/html/storage \
    && chmod -R 775 /var/www/html/bootstrap/cache

# Copy entrypoint script
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Expose port 80
EXPOSE 80

ENTRYPOINT ["entrypoint.sh"]
