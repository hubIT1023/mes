FROM php:8.3-apache

# Prevent interactive prompts during package install
ENV DEBIAN_FRONTEND=noninteractive

# Install system dependencies
RUN apt-get update && \
    apt-get install -y \
        curl \
        gnupg \
        unixodbc-dev \
        libssl-dev \
        libgssapi-krb5-2 \
    && rm -rf /var/lib/apt/lists/*

# Add Microsoft's official GPG key and APT repo (Debian 12 / bookworm)
RUN curl -fsSL https://packages.microsoft.com/keys/microsoft.asc | gpg --dearmor -o /usr/share/keyrings/microsoft.gpg && \
    echo "deb [arch=amd64 signed-by=/usr/share/keyrings/microsoft.gpg] https://packages.microsoft.com/debian/12/prod bookworm main" > /etc/apt/sources.list.d/microsoft-prod.list

# Install ODBC Driver 18 for SQL Server
RUN apt-get update && \
    ACCEPT_EULA=Y apt-get install -y msodbcsql18 && \
    rm -rf /var/lib/apt/lists/*

# Install sqlsrv and pdo_sqlsrv PHP extensions (5.12.0 supports PHP 8.3)
# Using `pecl install` without version is fine (latest compatible)
RUN pecl install sqlsrv pdo_sqlsrv && \
    docker-php-ext-enable sqlsrv pdo_sqlsrv

# Enable Apache mod_rewrite (commonly needed for apps)
RUN a2enmod rewrite

# Optional: Disable mpm_event and enable mpm_prefork if using mod_php (Apache + PHP module)
# This is often required for compatibility with older PHP apps
RUN a2dismod mpm_event && a2enmod mpm_prefork

# Copy application code
COPY src/ /var/www/html/

# Expose port (optional in Docker Compose)
EXPOSE 80