FROM php:8.1-apache

# Install system deps and PHP extensions
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libonig-dev \
    libzip-dev \
    zip \
    unzip \
    git \
    default-mysql-client \
 && docker-php-ext-install pdo pdo_mysql mysqli gd zip \
 && a2enmod rewrite

# Copy application code
COPY . /var/www/html/
WORKDIR /var/www/html

# Give the www-data user ownership of the app files that may need write access (uploads, images)
RUN chown -R www-data:www-data /var/www/html/assets /var/www/html/admin/assets 2>/dev/null || true

# Use a non-root www-data user where possible (default in php images)
EXPOSE 80

# Default CMD from base image will start Apache
