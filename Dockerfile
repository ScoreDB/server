FROM php:8.0-apache

# Change Apache configuration
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf && \
    ln -s /etc/apache2/mods-available/rewrite.load /etc/apache2/mods-enabled/rewrite.load

# Install dependencies
RUN apt-get update && \
    apt-get install unzip && \
    apt-get clean && \
    rm -rf /var/lib/apt/lists/*

# Install PHP extension
RUN pecl install redis-5.3.2 && \
    pecl install mongodb-1.9.0 && \
    docker-php-ext-enable redis mongodb

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Copy and set up project files
COPY . /var/www/html
RUN mkdir -p \
        bootstrap/cache \
        storage/app/public \
        storage/framework/cache/data \
        storage/framework/sessions \
        storage/framework/views \
        storage/logs && \
    touch database/database.sqlite && \
    chown www-data:www-data database/database.sqlite && \
    chown -R www-data:www-data \
        bootstrap/cache storage && \
    echo APP_KEY=>.env

# Database configuration
ENV DB_CONNECTION=sqlite

# Logging configuration
ENV LOGGING_CHANNEL=stderr

# GitHub integration configuration
ENV GITHUB_APP_ID=92513
ENV GITHUB_CLIENT_ID=Iv1.03655cb53b3ce79d

# Install dependencies and generate a key
RUN composer install --no-dev --no-cache && \
    php artisan key:generate

# Use production configuration
RUN cp "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"
