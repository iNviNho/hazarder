#!/bin/bash
chmod -R 777 /var/www/html/storage/logs && \
crontab /var/www/html/crons/root && \
cron && \
apache2-foreground