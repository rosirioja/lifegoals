FROM debian:jessie

RUN apt-get update && apt-get -y install php5 libapache2-mod-php5 php5-mcrypt php5-pgsql php5-mysql php5-curl curl git rsyslog vim nano --fix-missing

RUN curl -sS https://getcomposer.org/installer | php && \
    mv composer.phar /usr/local/bin/composer

COPY . /lifegoals

RUN cd /lifegoals && composer -n install --no-plugins --no-scripts

WORKDIR /lifegoals

VOLUME ["/lifegoals"]

EXPOSE 80

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=80"]
