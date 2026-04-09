#!/bin/sh
set -e

echo "Waiting for Postgres..."

until pg_isready -h "${DB_HOST:-postgres}" -p "${DB_PORT:-5432}" -U "${DB_USERNAME:-postgres}"; do
  echo "Postgres not ready yet..."
  sleep 1
done

echo "Postgres is ready!"

git config --global --add safe.directory /var/www

if [ ! -f .env ]; then
  echo "No .env found - copying from .env.example..."
  cp .env.example .env
fi

if [ ! -f vendor/autoload.php ]; then
  echo "Installing Composer dependencies..."
  composer install --no-interaction --prefer-dist
else
  echo "Composer dependencies already installed."
fi

if ! grep -q "^APP_KEY=" .env || [ -z "$(grep "^APP_KEY=" .env | cut -d "=" -f2)" ]; then
  echo "Generating APP_KEY..."
  php artisan key:generate --force
fi

echo "Starting Laravel server..."
exec php artisan serve --host=0.0.0.0 --port=8000
