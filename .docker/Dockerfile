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
    libxml2-dev \
    libcurl4-openssl-dev \
    libicu-dev \
    zlib1g-dev \
    zip \
    sockets \
    && docker-php-ext-install \
    intl \
    pdo \
    pdo_mysql \
    zip \
    opcache \
    xml \
    curl \
    ext-sockets \
    && rm -rf /var/lib/apt/lists/*

RUN git config --global user.email "chani@test.com" \ 
    && git config --global user.name "chani"

RUN a2ensite offre.local \
    #active la reecriture d'url sans index.php
    && a2enmod rewrite \
    && a2enmod ssl \
    && a2ensite offre.local-ssl




