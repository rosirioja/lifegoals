FROM debian:jessie

RUN apt-get update && apt-get -y install php5 libapache2-mod-php5 php5-mcrypt php5-pgsql php5-mysql php5-curl curl git rsyslog vim nano --fix-missing

COPY .env artisan composer.json phpunit.xml server.php phpspec.yml /var/www/lifegoals/

RUN groupadd -g 1000 www && \
    useradd -g www -u 1000 -r -M www && \
    cd /var/www/lifegoals && \
    curl -sS https://getcomposer.org/installer | php && \
    mv composer.phar /usr/local/bin/composer

COPY composer.json /var/www/lifegoals/

RUN composer -n install --no-plugins --no-scripts

COPY . /var/www/lifegoals

RUN chmod 777 -R /var/www/lifegoals/bootstrap 
RUN chmod 777 -R /var/www/lifegoals/storage 

WORKDIR /var/www/lifegoals

VOLUME ["/var/www/lifegoals"]

EXPOSE 80

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=80"]
