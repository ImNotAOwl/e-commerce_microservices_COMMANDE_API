#!/bin/bash
set -e

echo "Checking if the database is ready..."

echo "Checking if migrations need to be executed..."
if php bin/console doctrine:query:sql "SHOW TABLES" | grep -q "<mydatabase>"; then
    echo "Database already initialized, skipping migrations."
else
    echo "Database not initialized, running migrations..."
    php bin/console doctrine:migrations:migrate --no-interaction
fi

exec "$@"
