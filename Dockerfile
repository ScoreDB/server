FROM php:8.0-apache

# Install Tini
# https://github.com/krallin/tini
ADD https://github.com/krallin/tini/releases/download/v0.19.0/tini /sbin/tini
RUN chmod +x /sbin/tini
ENTRYPOINT ["/sbin/tini", "--", "docker-php-entrypoint"]

# Install dependencies
RUN apt-get update && \
    apt-get install unzip && \
    apt-get clean && \
    rm -rf /var/lib/apt/lists/*

# Install PHP extension
RUN pecl install mongodb-1.9.0 && \
    docker-php-ext-enable mongodb

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Copy project files
COPY . /var/www/html
RUN mkdir -p \
        bootstrap/cache \
        storage/app/public \
        storage/framework/cache/data \
        storage/framework/sessions \
        storage/framework/views \
        storage/logs && \
    touch \
        database/database.sqlite \
        .env

ENV APP_ENV=production
ENV DB_CONNECTION=sqlite
ENV CACHE_DRIVER=file
ENV SESSION_DRIVER=file
ENV GITHUB_APP_ID=92513
ENV GITHUB_CLIENT_ID=Iv1.03655cb53b3ce79d

# Install dependencies
RUN composer install --no-dev --no-cache && \
    php artisan key:generate

# Use production configuration
RUN cp "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"
