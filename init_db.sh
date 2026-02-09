#!/bin/bash
set -e

DB_HOST="db"
DB_USER="root"
DB_PASS="gamelena"
DB_NAME="tangerine"

echo "Waiting for MySQL to be ready..."
until mysql -h"$DB_HOST" -u"$DB_USER" -p"$DB_PASS" -e "SELECT 1" &> /dev/null; do
  echo "MySQL is unavailable - sleeping"
  sleep 1
done

echo "MySQL is up - executing initialization scripts..."

# Load Schema
echo "Loading createdb.sql..."
mysql -h"$DB_HOST" -u"$DB_USER" -p"$DB_PASS" < db/createdb.sql

echo "Loading tangerine.sql..."
mysql -h"$DB_HOST" -u"$DB_USER" -p"$DB_PASS" "$DB_NAME" < db/tangerine.sql

echo "Loading basicdata.sql..."
mysql -h"$DB_HOST" -u"$DB_USER" -p"$DB_PASS" "$DB_NAME" < db/basicdata.sql

echo "Loading privileges.sql..."
mysql -h"$DB_HOST" -u"$DB_USER" -p"$DB_PASS" "$DB_NAME" < db/privileges.sql

echo "Database initialization complete."
