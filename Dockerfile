FROM php:7.2-apache

RUN apt-get update

# 1. development packages
RUN apt-get install -y \
	vim \
    g++ 
    

# 2. apache configs + document root
RUN echo "ServerName opencart" >> /etc/apache2/apache2.conf


RUN adduser -D admin apache -h /var/www/opencart
WORKDIR /var/www/opencart

COPY . /var/www/opencart/
COPY docker-entrypoint.sh /usr/local/bin/
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint
COPY docker-php-entrypoint.sh /usr/local/bin/docker-php-entrypoint


ENV APACHE_DOCUMENT_ROOT=/var/www/opencart
#ENV HTTP_SERVER_ENV=http://localhost:8085/
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

ADD 000-default.conf /etc/apache2/sites-available/000-default.conf


# 3. mod_rewrite for URL rewrite and mod_headers for .htaccess extra headers like Access-Control-Allow-Origin-
RUN a2enmod rewrite headers

# 4. start with base php config, then add extensions
#RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

ENV DB_DATABASE='opencart'
ENV DB_HOST='192.168.10.38'
ENV DB_USERNAME='root'
ENV DB_PASSWORD='root'

RUN docker-php-ext-install \
    pdo_mysql \
    mysqli
    
#gd library installation   
RUN apt-get update && apt-get install -y libpng-dev 
RUN apt-get install -y \
    libwebp-dev \
    libjpeg62-turbo-dev \
    libpng-dev libxpm-dev \
    libfreetype6-dev

RUN docker-php-ext-configure gd \
    --with-gd \
    --with-webp-dir \
    --with-jpeg-dir \
    --with-png-dir \
    --with-zlib-dir \
    --with-xpm-dir \
    --with-freetype-dir 

RUN docker-php-ext-install gd

# Add user for laravel application
RUN groupadd -g 1000 www
RUN useradd -u 1000 -ms /bin/bash -g www www


# Copy existing application directory permissions
COPY --chown=www:www . /var/www

RUN  chmod -R 777 /var/www/opencart
RUN chmod -R 777  /usr/local/bin/docker-entrypoint.sh
RUN chmod -R 777  /usr/local/bin/docker-entrypoint
RUN chmod -R 777  /usr/local/bin/docker-php-entrypoint

#RUN ln -s /usr/local/bin/docker-entrypoint.sh /
#ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]

#ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]



# 5. composer
#COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 6. we need a user with the same UID/GID with host user
# so when we execute CLI commands, all the host file's permissions and ownership remains intact
# otherwise command from inside container will create root-owned files and directories
#ARG uid
#EXPOSE 8080 443
