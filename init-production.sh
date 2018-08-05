#!/bin/bash
cp -r /deploy/vendor /var/www/html && \
chmod -R 777 /var/www/html/storage/logs && \
php composer.phar install && \
php artisan migrate --force && \
crontab /var/www/html/crons/root && \
cron && \
apache2-foreground
