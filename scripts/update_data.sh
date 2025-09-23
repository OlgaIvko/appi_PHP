#!/bin/bash
echo "$(date): Update script executed" >> /var/log/update.log
cd /var/www/html
php artisan update:data >> /var/log/update.log 2>&1



