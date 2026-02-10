FROM php:8.1-apache

# Fix for Debian Buster EOL archives (Not needed for 8.1/Bullseye+)
# RUN echo "deb [trusted=yes] http://archive.debian.org/debian buster main" > /etc/apt/sources.list && \
#     echo "deb [trusted=yes] http://archive.debian.org/debian-security buster/updates main" >> /etc/apt/sources.list && \
#     echo "Acquire::Check-Valid-Until false;" > /etc/apt/apt.conf.d/99no-check-valid-until && \
#     echo "Acquire::AllowInsecureRepositories true;" > /etc/apt/apt.conf.d/99allow-insecure && \
#     echo "Acquire::AllowDowngradeToInsecureRepositories true;" >> /etc/apt/apt.conf.d/99allow-insecure

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    gnupg \
    libpng-dev \
    libmcrypt-dev \
    libxml2-dev \
    libssl-dev \
    libonig-dev \
    libxslt-dev \
    libzip-dev \
    libjpeg-dev \
    mariadb-client \
    && rm -rf /var/lib/apt/lists/*

# Install Node.js (for Bower) - Debian Buster has old node, but sufficient for bower
RUN apt-get update && apt-get install -y nodejs npm

# Install Bower globally
RUN npm install -g bower

# Configure GD with jpeg support
RUN docker-php-ext-configure gd --with-jpeg

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_mysql mysqli gd zip soap mbstring xsl

# Install Mcrypt (deprecated in 7.2, removed in 8.0+)
# RUN pecl install mcrypt-1.0.4 && docker-php-ext-enable mcrypt

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Configure Apache DocumentRoot
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/000-default.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Grant permissions for bower_components (needed for Alias/Symlink)
RUN echo "<Directory /var/www/html/bower_components/>" >> /etc/apache2/apache2.conf && \
    echo "    Options Indexes FollowSymLinks" >> /etc/apache2/apache2.conf && \
    echo "    AllowOverride All" >> /etc/apache2/apache2.conf && \
    echo "    Require all granted" >> /etc/apache2/apache2.conf && \
    echo "</Directory>" >> /etc/apache2/apache2.conf

# Create Bootstrap script
COPY bootstrap.sh /usr/local/bin/bootstrap.sh
RUN chmod +x /usr/local/bin/bootstrap.sh

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
ENV COMPOSER_ALLOW_SUPERUSER 1

WORKDIR /var/www/html

# Prepare patch script
COPY tasks/zend-test-patch.sh /usr/local/bin/zend-test-patch.sh
RUN chmod +x /usr/local/bin/zend-test-patch.sh

# Create log directories and set permissions
RUN mkdir -p /var/www/html/log/debug && \
    mkdir -p /var/www/html/cache && \
    chown -R www-data:www-data /var/www/html/log /var/www/html/cache && \
    chmod -R 777 /var/www/html/log /var/www/html/cache
