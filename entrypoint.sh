#!/bin/bash
# Run Magento setup commands
cd /var/www/html/public
composer install
php bin/magento setup:upgrade
php bin/magento setup:di:compile
php bin/magento cache:flush

