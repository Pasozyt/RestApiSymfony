#!/bin/bash

HTTPDUSER=www-data

set -e
cd /application

composer install --no-interaction && composer dump-autoload --optimize --classmap-authoritative
#php bin/console doctrine:migration:migrate --no-interaction

npm install --silent
npm run build

php-fpm
