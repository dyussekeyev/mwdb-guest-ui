# Use the official PHP image as the base image
FROM php:8.1-apache

# Install GD library and its dependencies if your application needs image processing features
RUN apt-get update && apt-get install -y \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd

# Copy the application files to the /var/www/html directory in the container
COPY . /var/www/html/

# Set the working directory
WORKDIR /var/www/html

# Expose port 80 to the host
EXPOSE 80

# Enable the Apache rewrite module
RUN a2enmod rewrite

# Set permissions for the Apache server
RUN chown -R www-data:www-data /var/www/html

# Start Apache server
CMD ["apache2-foreground"]
