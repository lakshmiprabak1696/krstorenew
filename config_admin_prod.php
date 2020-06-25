<?php
// HTTP
$http_server=$_SERVER['HTTP_HOST'];
define('HTTP_SERVER', 'http://'.$http_server.'/admin/');
define('HTTP_CATALOG', 'http://'.$http_server.'/');

// HTTPS
define('HTTPS_SERVER', 'https://'.$http_server.'/admin/');
define('HTTPS_CATALOG', 'https://'.$http_server.'/');

// DIR
define('DIR_APPLICATION', '/var/www/opencart/admin/');
define('DIR_SYSTEM', '/var/www/opencart/system/');
define('DIR_IMAGE', '/var/www/opencart/image/');
define('DIR_STORAGE', DIR_SYSTEM . 'storage/');
define('DIR_CATALOG', '/var/www/opencart/catalog/');
define('DIR_LANGUAGE', DIR_APPLICATION . 'language/');
define('DIR_TEMPLATE', DIR_APPLICATION . 'view/template/');
define('DIR_CONFIG', DIR_SYSTEM . 'config/');
define('DIR_CACHE', DIR_STORAGE . 'cache/');
define('DIR_DOWNLOAD', DIR_STORAGE . 'download/');
define('DIR_LOGS', DIR_STORAGE . 'logs/');
define('DIR_MODIFICATION', DIR_STORAGE . 'modification/');
define('DIR_SESSION', DIR_STORAGE . 'session/');
define('DIR_UPLOAD', DIR_STORAGE . 'upload/');

// DB
define('DB_DRIVER', 'mysqli');
define('DB_HOSTNAME', 'env_db_host');
define('DB_USERNAME', 'env_db_username');
define('DB_PASSWORD', 'env_db_password');
define('DB_DATABASE', 'env_db_database');
define('DB_PORT', '3306');
define('DB_PREFIX', 'oc_');

// OpenCart API
define('OPENCART_SERVER', 'https://www.opencart.com/');
