FROM php:8.3-apache

ENV DEBIAN_FRONTEND=noninteractive

# Install system deps + PostgreSQL client
RUN apt-get update && \
    apt-get install -y \
        libpq-dev \
        git \
        zip \
        unzip \
    && rm -rf /var/lib/apt/lists/*

# Install PHP PostgreSQL extensions
RUN docker-php-ext-install pdo_pgsql pgsql

# Enable Apache modules
RUN a2enmod rewrite
RUN a2dismod mpm_event && a2enmod mpm_prefork

# Copy application code
COPY app/ /var/www/html/

EXPOSE 80
