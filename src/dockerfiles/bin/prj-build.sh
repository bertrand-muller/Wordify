#!/bin/bash

# Modify access rights 
echo -e "Modify access rights on project. \e[33mStarting\e[0m" 
chmod -R 777 .
echo -e "Modify access rights on project. \e[32mDone\e[0m"
echo ""


# Copy example environment file
echo -e "Copy example environment file. \e[33mStarting\e[0m"
cp .env.example .env
cat .env
echo -e "Copy example environment file. \e[32mDone\e[0m"
echo ""


# Install dependencies
echo -e "Install PHP dependencies. \e[33mStarting\e[0m"
composer install
echo -e "Install PHP dependencies. \e[32mDone\e[0m"
echo ""


# Generate application key
echo -e "Generate application key. \e[33mStarting\e[0m"
php artisan key:generate
echo -e "Generate application key. \e[32mDone\e[0m"
echo ""


# Configure cache
echo -e "Configure Laravel cache. \e[33mStarting\e[0m"
php artisan config:cache
echo -e "Configure Laravel cache. \e[32mDone\e[0m"
echo ""


# Install dependencies
echo -e "Install JS dependencies. \e[33mStarting\e[0m"
yarn install --no-bin-links
yarn add cross-env --no-bin-links
yarn global add cross-env --no-bin-links
yarn run dev
echo -e "Install JS dependencies. \e[32mDone\e[0m"
echo ""


# Create database tables and populate seed data
echo -e "Create database tables and populate them. \e[33mStarting\e[0m"
php artisan migrate --seed
echo -e "Create database tables and populate them. \e[32mDone\e[0m"
echo ""
