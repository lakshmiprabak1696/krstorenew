#!/bin/sh
set -e

# first arg is `-f` or `--some-option`
if [ "${1#-}" != "$1" ]; then
        set -- apache2-foreground "$@"
fi

db_host=${DB_HOST}
db_name=${DB_DATABASE}
db_username=${DB_USERNAME}
db_password=${DB_PASSWORD}
cp /var/www/opencart/config_root_prod.php /var/www/opencart/config.php
cp /var/www/opencart/config_admin_prod.php /var/www/opencart/admin/config.php
cp /var/www/opencart/mysqli_prod.php /var/www/opencart/system/library/db/mysqli.php
sed -ri -e "s!env_db_host!$db_host!g" /var/www/opencart/config.php
sed -ri -e "s!env_db_database!$db_name!g" /var/www/opencart/config.php
sed -ri -e "s!env_db_username!$db_username!g" /var/www/opencart/config.php
sed -ri -e "s!env_db_password!$db_password!g" /var/www/opencart/config.php

sed -ri -e "s!env_db_host!$db_host!g" /var/www/opencart/admin/config.php
sed -ri -e "s!env_db_database!$db_name!g" /var/www/opencart/admin/config.php
sed -ri -e "s!env_db_username!$db_username!g" /var/www/opencart/admin/config.php
sed -ri -e "s!env_db_password!$db_password!g" /var/www/opencart/admin/config.php
exec "$@";