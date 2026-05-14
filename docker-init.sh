#!/bin/bash
set -e

# Helper to wait for MySQL to be ready
DB_HOST=${DB_HOST:-db}
DB_PORT=${DB_PORT:-3306}
RETRIES=30

echo "Waiting for MySQL at $DB_HOST:$DB_PORT..."
count=0
until mysqladmin ping -h"$DB_HOST" -P"$DB_PORT" --silent; do
  count=$((count+1))
  if [ $count -ge $RETRIES ]; then
    echo "MySQL did not become available in time" >&2
    exit 1
  fi
  sleep 1
done

# Run the setup script to create database/tables if needed
if [ -f /var/www/html/setup_database.php ]; then
  echo "Running setup_database.php"
  php /var/www/html/setup_database.php
fi

echo "Database initialization complete."