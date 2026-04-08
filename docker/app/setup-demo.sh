#!/bin/sh
set -e

echo "Waiting for Postgres..."

until pg_isready -h "${DB_HOST:-postgres}" -p "${DB_PORT:-5432}" -U "${DB_USERNAME:-postgres}"; do
  echo "Postgres not ready yet..."
  sleep 1
done

echo "Postgres is ready!"

if [ ! -f .env ]; then
  echo "No .env found - copying from .env.example..."
  cp .env.example .env
fi

if ! grep -q "^APP_KEY=" .env || [ -z "$(grep "^APP_KEY=" .env | cut -d "=" -f2)" ]; then
  echo "Generating APP_KEY..."
  php artisan key:generate --force
fi

echo "Running demo bootstrap..."
php artisan migrate --force
php artisan products:import
php artisan db:seed --class=OrderSeeder --force

echo "Demo bootstrap completed successfully!"
