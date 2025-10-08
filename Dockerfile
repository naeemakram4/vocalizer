FROM php:8.2-apache

# Install dependencies
RUN apt-get update && apt-get install -y \
    git curl zip unzip libzip-dev \
    nodejs npm \
    && docker-php-ext-install pdo zip

# Enable Apache rewrite
RUN a2enmod rewrite

# Set public/ as the web root
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/000-default.conf

# Copy app files
COPY . /var/www/html

# Set working directory
WORKDIR /var/www/html

# Install Composer
ENV COMPOSER_ALLOW_SUPERUSER=1
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
RUN composer install --no-dev --optimize-autoloader --no-scripts

# Build frontend assets
RUN npm install && npm run build

# Set permissions
RUN chown -R www-data:www-data /var/www/html


# Expose port
EXPOSE 80

COPY entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh
CMD ["entrypoint.sh"]
