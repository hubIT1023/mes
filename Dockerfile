FROM php:8.3-apache

ENV DEBIAN_FRONTEND=noninteractive

# Install system deps
RUN apt-get update && \
    apt-get install -y \
        curl \
        gnupg \
        unixodbc-dev \
        libssl-dev \
        libgssapi-krb5-2 \
    && rm -rf /var/lib/apt/lists/*

# Add Microsoft GPG key + repo
RUN curl -fsSL https://packages.microsoft.com/keys/microsoft.asc | gpg --dearmor -o /usr/share/keyrings/microsoft.gpg && \
    echo "deb [arch=amd64 signed-by=/usr/share/keyrings/microsoft.gpg] https://packages.microsoft.com/debian/12/prod bookworm main" > /etc/apt/sources.list.d/microsoft-prod.list

# Install ODBC Driver 18
RUN apt-get update && \
    ACCEPT_EULA=Y apt-get install -y msodbcsql18 && \
    rm -rf /var/lib/apt/lists/*

# Install PHP SQL Server extensions
RUN pecl install sqlsrv pdo_sqlsrv && \
    docker-php-ext-enable sqlsrv pdo_sqlsrv

# Apache config
RUN a2enmod rewrite
RUN a2dismod mpm_event && a2enmod mpm_prefork

# Copy app (no vendor/, no composer)
COPY app/ /var/www/html/

EXPOSE 80