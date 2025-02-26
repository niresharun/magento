#!/bin/bash
# Run Magento setup commands
php bin/magento setup:upgrade
php bin/magento setup:di:compile
php bin/magento cache:flush

# Start PHP-FPM in the background
php-fpm -D

# Start Nginx in the foreground
nginx -g "daemon off;"