FROM php:7.0-apache

MAINTAINER Alex Marston <alexander.marston@gmail.com>

COPY ./app/ /var/www/html/

RUN mkdir -p /var/lib/vnstat
