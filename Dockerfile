FROM php:8.3-apache

ENV DEBIAN_FRONTEND=noninteractive

# Install only necessary system dependencies
RUN apt-get update && \
    apt-get install -y \
        libpq-dev \
        libpng-dev \
        libjpeg-dev \
        git \
        zip \
        unzip \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions (PostgreSQL + GD for image compression)
RUN docker-php-ext-configure gd --with-jpeg --with-webp && \
    docker-php-ext-install pdo_pgsql pgsql gd

# Enable required Apache modules
RUN a2enmod rewrite headers ssl

# Disable default site to avoid conflicts
RUN a2dissite 000-default

# Enable your custom site (config is mounted via volume)
RUN a2ensite hubit.conf

# Ensure web root exists (no COPY needed — volumes provide content)
RUN mkdir -p /var/www/html

# Expose both HTTP and HTTPS (you use Let's Encrypt)
EXPOSE 80 443