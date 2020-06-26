#!/bin/sh
db_host=${DB_HOST}
db_name=${DB_DATABASE}
db_username=${DB_USERNAME}
db_password=${DB_PASSWORD}
sed -ri -e "s!env_db_host!$db_host!g" /var/www/opencart/config.php
sed -ri -e "s!env_db_database!$db_name!g" /var/www/opencart/config.php
sed -ri -e "s!env_db_username!$db_username!g" /var/www/opencart/config.php
sed -ri -e "s!env_db_password!$db_password!g" /var/www/opencart/config.php

sed -ri -e "s!env_db_host!$db_host!g" /var/www/opencart/admin/config.php
sed -ri -e "s!env_db_database!$db_name!g" /var/www/opencart/admin/config.php
sed -ri -e "s!env_db_username!$db_username!g" /var/www/opencart/admin/config.php
sed -ri -e "s!env_db_password!$db_password!g" /var/www/opencart/admin/config.php
exec "$@";