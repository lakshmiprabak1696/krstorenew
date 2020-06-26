#!/bin/sh
set -e

# first arg is `-f` or `--some-option`
if [ "${1#-}" != "$1" ]; then
        set -- apache2-foreground "$@"
fi

db_host1=${DB_HOST1}
db_name1=${DB_DATABASE1}
db_username1=${DB_USERNAME1}
db_password1=${DB_PASSWORD1}
cp /var/www/opencart/config_root_prod.php /var/www/opencart/config.php
cp /var/www/opencart/config_admin_prod.php /var/www/opencart/admin/config.php
#cp /var/www/opencart/mysqli_prod.php /var/www/opencart/system/library/db/mysqli.php
sed -ri -e "s!env_db_host!$db_hos1t!g" /var/www/opencart/config.php
sed -ri -e "s!env_db_database!$db_name1!g" /var/www/opencart/config.php
sed -ri -e "s!env_db_username!$db_username1!g" /var/www/opencart/config.php
#sed -ri -e "s!env_db_password!$db_password1!g" /var/www/opencart/config.php

sed -ri -e "s!env_db_host!$db_host1!g" /var/www/opencart/admin/config.php
sed -ri -e "s!env_db_database!$db_name1!g" /var/www/opencart/admin/config.php
sed -ri -e "s!env_db_username!$db_username1!g" /var/www/opencart/admin/config.php
#sed -ri -e "s!env_db_password!$db_password1!g" /var/www/opencart/admin/config.php
exec "$@";