FROM php:8.2-apache

RUN docker-php-ext-install mysqli pdo_mysql

COPY src/ /var/www/html/
COPY images/ /var/www/html/images/

RUN chown -R www-data:www-data /var/www/html && \
    echo 'DirectoryIndex home.php' > /etc/apache2/conf-available/zz-directoryindex.conf && \
    a2enconf zz-directoryindex && \
    chmod 644 /var/www/html/*.php /var/www/html/*.css && \
    chmod 644 /var/www/html/includes/*.php /var/www/html/administrator/*.php

# Tunggu DB siap lalu seed flag/admin, baru jalankan apache
ADD https://github.com/jwilder/dockerize/releases/download/v0.9.3/dockerize-linux-amd64-v0.9.3.tar.gz /tmp/dockerize.tgz
RUN tar -xzf /tmp/dockerize.tgz -C /usr/local/bin dockerize && rm /tmp/dockerize.tgz

CMD ["sh", "-c", "dockerize -wait tcp://db:3306 -timeout 60s && php /var/www/html/includes/seed.php && apache2-foreground"]
