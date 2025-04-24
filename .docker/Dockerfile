FROM php:8.2-apache

RUN mkdir /var/www/offre.local

WORKDIR /var/www/offre.local

COPY ./apache/offre.local.conf /etc/apache2/sites-available/offre.local.conf
COPY ./apache/ssl/offre.local-ssl.conf /etc/apache2/sites-available/offre.local-ssl.conf

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libpq-dev \
    libonig-dev \
    libzip-dev \
    zip \
    && docker-php-ext-install pdo pdo_mysql zip

RUN curl -sS https://get.symfony.com/cli/installer | bash
RUN mv /root/.symfony5/bin/symfony /usr/local/bin/symfony
RUN git config --global user.email "chani@test.com" \ 
    && git config --global user.name "chani"

# RUN docker-php-ext-install mysqli pdo pdo_mysql zip unzip && docker-php-ext-enable mysqli pdo_mysql zip unzip
RUN a2ensite offre.local \
    #active la reecriture d'url sans index.php
    && a2enmod rewrite \
    && a2enmod ssl \
    && a2ensite offre.local-ssl




