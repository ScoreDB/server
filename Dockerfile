FROM php:8.0-apache

# Install Tini
# https://github.com/krallin/tini
ADD https://github.com/krallin/tini/releases/download/v0.19.0/tini /sbin/tini
RUN chmod +x /sbin/tini
ENTRYPOINT ["/sbin/tini", "--", "docker-php-entrypoint"]

# Install PHP MongoDB extension
ADD https://pecl.php.net/get/mongodb-1.9.0.tgz /tmp/mongodb.tgz
RUN pecl install /tmp/mongodb.tgz && \
    docker-php-ext-enable mongodb && \
    rm /tmp/mongodb.tgz

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Copy project files
COPY . /var/www/html

# Install dependencies
RUN composer install --no-dev --no-cache

# Use production configuration
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"
