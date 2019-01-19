#!/bin/bash

# Copy example environment file
cp .env.example .env

# Install dependencies
composer install --prefer-dist --no-interaction

# Generate application key
php artisan key:generate

# Verify environment config
cat .env

# Install dependencies
npm install
npm run dev

# Generate assets
npm run watch

# Create database tables and populate seed data
php artisan migrate --seed --no-interaction

# Execute PHPUnit tests
vendor/bin/phpunit
