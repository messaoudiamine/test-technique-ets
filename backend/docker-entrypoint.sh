#!/bin/bash
set -e

# Create directories if they don't exist
mkdir -p /app/var/cache/dev /app/var/cache/prod /app/var/log /app/config/jwt

# Fix permissions for var directory
chown -R www-data:www-data /app/var || true
chmod -R 775 /app/var || true

# Fix permissions for config/jwt directory
chown -R www-data:www-data /app/config/jwt || true
chmod -R 775 /app/config/jwt || true

# Execute the main command
exec "$@"

