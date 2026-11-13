FROM php:8.3-apache

# Install dependencies for SimpleSAMLphp
RUN apt-get update \
    && apt-get install -y --no-install-recommends \
       libonig-dev \
       libxml2-dev \
       unzip \
       git \
    && docker-php-ext-install bcmath mbstring \
    && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

# Install a pinned SimpleSAMLphp release
RUN composer create-project \
    --no-dev \
    --prefer-dist \
    simplesamlphp/simplesamlphp:2.5.4 \
    /var/simplesamlphp

# Create writable cache directory for SimpleSAMLphp
RUN mkdir -p /var/cache/simplesamlphp \
    && chown www-data:www-data /var/cache/simplesamlphp

# Configure Apache
COPY apache.conf /etc/apache2/sites-available/000-default.conf

# Enable Apache URL rewriting
RUN a2enmod rewrite

WORKDIR /var/simplesamlphp

EXPOSE 80
