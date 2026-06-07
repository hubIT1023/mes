#!/bin/bash
set -e

# Path to Apache site config
CONF_FILE="/etc/apache2/sites-available/hubit.conf"

# Extract domains from apache config if present
if [ -f "$CONF_FILE" ]; then
    echo "Parsing domains from $CONF_FILE..."
    MAIN_DOMAIN=$(grep -i "ServerName" "$CONF_FILE" | awk '{print $2}' | head -n 1)
    # Get all aliases
    ALIASES=$(grep -i "ServerAlias" "$CONF_FILE" | sed 's/ServerAlias//I' | xargs)
else
    MAIN_DOMAIN="hubit.online"
    ALIASES="www.hubit.online pgadmin.hubit.online"
fi

# Fallback values if not set
CERTBOT_EMAIL=${CERTBOT_EMAIL:-"admin@$MAIN_DOMAIN"}
CERTBOT_STAGING=${CERTBOT_STAGING:-"false"}

echo "Main Domain: $MAIN_DOMAIN"
echo "Aliases: $ALIASES"
echo "Email: $CERTBOT_EMAIL"
echo "Staging: $CERTBOT_STAGING"

# Construct Certbot domains arguments
CERTBOT_DOMAINS="-d $MAIN_DOMAIN"
for alias in $ALIASES; do
    CERTBOT_DOMAINS="$CERTBOT_DOMAINS -d $alias"
done

CERT_DIR="/etc/letsencrypt/live/$MAIN_DOMAIN"

# Check if certificates exist
if [ ! -f "$CERT_DIR/fullchain.pem" ]; then
    echo "No SSL certificate found for $MAIN_DOMAIN. Generating dummy certificate to allow Apache to start..."
    mkdir -p "$CERT_DIR"
    openssl req -x509 -nodes -newkey rsa:2048 -days 365 \
      -keyout "$CERT_DIR/privkey.pem" \
      -out "$CERT_DIR/fullchain.pem" \
      -subj "/CN=$MAIN_DOMAIN"
    DUMMY_CERT=true
else
    echo "Existing SSL certificate found for $MAIN_DOMAIN."
    DUMMY_CERT=false
fi

# Start background process for certbot setup and renewal
(
    # Wait for Apache to start (wait up to 30 seconds)
    echo "Waiting for Apache to boot..."
    for i in {1..15}; do
        if curl -s -k https://localhost > /dev/null || curl -s http://localhost > /dev/null; then
            echo "Apache is running."
            break
        fi
        sleep 2
    done

    if [ "$DUMMY_CERT" = "true" ]; then
        echo "Requesting real certificate from Let's Encrypt..."
        
        STAGING_FLAG=""
        if [ "$CERTBOT_STAGING" = "true" ]; then
            STAGING_FLAG="--staging"
            echo "Using staging environment..."
        fi

        # Run certbot to get real certificates
        # Use webroot plugin so it serves the challenge via port 80
        certbot certonly --webroot -w /var/www/html \
          $STAGING_FLAG \
          $CERTBOT_DOMAINS \
          --email "$CERTBOT_EMAIL" \
          --agree-tos --no-eff-email --non-interactive \
          --keep-until-expiring

        if [ $? -eq 0 ]; then
            echo "Successfully obtained real certificate. Reloading Apache..."
            apachectl graceful
        else
            echo "Failed to obtain real certificate. Running with dummy certificate."
        fi
    fi

    # Start renewal loop (runs every 12 hours)
    while :; do
        echo "Checking for renewal..."
        certbot renew --webroot -w /var/www/html --deploy-hook "apachectl graceful" --non-interactive
        sleep 12h
    done
) &

# Execute default Apache command
echo "Starting Apache in foreground..."
exec apache2-foreground
