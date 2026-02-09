#!/bin/sh
set -e

DOMAIN="${DOMAIN:-localhost}"

# Create self-signed cert if Let's Encrypt cert doesn't exist (so nginx can start on 443)
CERT_DIR="/etc/letsencrypt/live/${DOMAIN}"
if [ ! -f "${CERT_DIR}/fullchain.pem" ]; then
    echo "No cert at ${CERT_DIR}; creating self-signed certificate for ${DOMAIN}"
    mkdir -p "${CERT_DIR}"
    openssl req -x509 -nodes -days 1 -newkey rsa:2048 \
        -keyout "${CERT_DIR}/privkey.pem" \
        -out "${CERT_DIR}/fullchain.pem" \
        -subj "/CN=${DOMAIN}"
fi

# Generate nginx config from template (DOMAIN substituted)
export DOMAIN
envsubst '${DOMAIN}' < /etc/nginx/templates/nginx.conf.template > /etc/nginx/conf.d/default.conf

exec nginx -g 'daemon off;'
