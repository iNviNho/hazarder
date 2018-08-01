#!/bin/sh
cd /var/www/hazarder/ && git pull && ./deploy-production.sh && docker restart hazarder
