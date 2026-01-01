FROM php:8.3-apache

ENV DEBIAN_FRONTEND=noninteractive

# Install system dependencies
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libfreetype6-dev \
    libjpeg-dev \
    libpng-dev \
    git \
    zip \
    unzip \
    certbot \
    python3-certbot-apache \
    && rm -rf /var/lib/apt/lists/*

# Configure and install PHP extensions
RUN docker-php-ext-configure gd \
        --with-jpeg \
        --with-freetype \
    && docker-php-ext-install \
        pdo_pgsql \
        pgsql \
        gd

# Enable Apache modules
RUN a2enmod rewrite proxy proxy_http ssl headers

# Allow .htaccess and index.php
RUN echo "<Directory /var/www/html>\n\
    AllowOverride All\n\
    DirectoryIndex index.php\n\
</Directory>" >> /etc/apache2/sites-available/000-default.conf

# Copy Apache virtual host config
COPY apache/sites-available/hubit.conf /etc/apache2/sites-available/hubit.conf

# Enable the site
RUN a2ensite hubit.conf

# Copy application code LAST
COPY . /var/www/html/

EXPOSE 80 443
