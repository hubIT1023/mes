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

# Install sqlsrv and pdo_sqlsrv PHP extensions
RUN pecl install sqlsrv pdo_sqlsrv && \
    docker-php-ext-enable sqlsrv pdo_sqlsrv

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Optional: Disable mpm_event and enable mpm_prefork
RUN a2dismod mpm_event && a2enmod mpm_prefork

# --- Install Composer ---
# Download and install Composer globally
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# --- Install PHP Dependencies ---
# Copy only composer files first for caching
COPY app/composer.json app/composer.lock /var/www/html/

# Run composer install in the app directory
WORKDIR /var/www/html
RUN composer install --no-dev --optimize-autoloader

# --- Copy Application Code ---
# Now copy the rest of the app code (excluding vendor/, which is already built)
COPY app/ /var/www/html/

# Expose port (optional in Docker Compose)
EXPOSE 80