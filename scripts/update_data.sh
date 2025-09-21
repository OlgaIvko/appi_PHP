#!/bin/bash
cd /var/www/html
php artisan update:data >> /var/log/update.log 2>&1
