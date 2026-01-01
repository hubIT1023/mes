FROM php:8.3-apache

ENV DEBIAN_FRONTEND=noninteractive

# Install system dependencies (including GD requirements + existing ones)
RUN apt-get update && \
    apt-get install -y \
        libpq-dev \
        libfreetype-dev \
        libjpeg-dev \
        libpng-dev \
        git \
        zip \
        unzip \
        certbot \
        python3-certbot-apache \
    && rm -rf /var/lib/apt/lists/*

# Install and enable PHP extensions: PostgreSQL + GD
RUN docker-php-ext-install pdo_pgsql pgsql gd && \
    docker-php-ext-enable pdo_pgsql pgsql gd

# Enable Apache modules
RUN a2enmod rewrite proxy proxy_http ssl headers

# Configure Apache to allow .htaccess and use index.php
RUN echo "    <Directory /var/www/html>\n        AllowOverride All\n        DirectoryIndex index.php\n    </Directory>" >> /etc/apache2/sites-available/000-default.conf

# Copy Apache virtual host config
COPY apache/sites-available/hubit.conf /etc/apache2/sites-available/hubit.conf

# Enable the site
RUN a2ensite hubit.conf

# Copy application code LAST (to leverage Docker layer caching)
COPY . /var/www/html/

EXPOSE 80 443