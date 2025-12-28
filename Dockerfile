FROM php:8.3-apache

ENV DEBIAN_FRONTEND=noninteractive

# Install system deps + certbot
RUN apt-get update && \
    apt-get install -y \
        libpq-dev \
        git \
        zip \
        unzip \
        certbot \
        python3-certbot-apache \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo_pgsql pgsql

# Enable Apache modules
RUN a2enmod rewrite proxy proxy_http ssl headers

# Allow .htaccess overrides
RUN echo "    <Directory /var/www/html>\n        AllowOverride All\n    </Directory>" >> /etc/apache2/sites-available/000-default.conf

# Copy ENTIRE PROJECT (so index.php, .htaccess, app/ are all included)
COPY . /var/www/html/

# Copy Apache vhost config
COPY apache/sites-available/hubit.conf /etc/apache2/sites-available/hubit.conf

# Enable the site
RUN a2ensite hubit.conf

EXPOSE 80 443
