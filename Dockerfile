FROM php:7.0-apache

RUN mkdir -p /var/www/html/vnstat/
ADD . /var/www/html/vnstat/
RUN mkdir -p /var/lib/vnstat