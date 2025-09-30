# Use the official PHP image as the base image
FROM php:8.1-apache

# Install GD library and its dependencies if your application needs image processing features
RUN apt-get update && apt-get install -y \
    libfreetype-dev=2.13.3+dfsg-1 \
    libjpeg62-turbo-dev=1:2.1.5-4 \
    libpng-dev=1.6.48-1 \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd \
    && rm -rf /var/lib/apt/lists/*

# Copy the application files to the /var/www/html directory in the container
COPY . /var/www/html/

# Copy .env file to the container
COPY .env /var/www/html/.env

# Add PHP upload limits (10MB) config
COPY uploads.ini /usr/local/etc/php/conf.d/

# Set the working directory
WORKDIR /var/www/html

# Expose port 80 to the host
EXPOSE 80

# Enable the Apache rewrite module
RUN a2enmod rewrite

# Set permissions for the Apache server
RUN chown -R www-data:www-data /var/www/html

# Switch to non-root user (www-data)
USER www-data

# Start Apache server
CMD ["apache2-foreground"]
