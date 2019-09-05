# What is vnstat-dashboard?
This dashboard is an adaptation of vnstat-php-frontend by bjd using Bootstrap written in PHP. It provides the following:

* Hourly Statistics Chart (using Google Charts)
* Daily & Monthly Statistics Overview
* Top 10 Day Statistics
* Automatically populated interface selection

## Run it with Docker

### How to build it
``$ docker build . -t amarston/vnstat-dashboard:latest``

### How to publish it
``$ docker push amarston/vnstat-dashboard:latest``

### How to start it
``$ docker run --name vnstat-dashboard -p 80:80 -v /usr/bin/vnstat:/usr/bin/vnstat -v /var/lib/vnstat:/var/lib/vnstat -d amarston/vnstat-dashboard:latest``

### How to stop it
``$ docker stop vnstat-dashboard``

## Run it with Locally

### How to run it
```
$ cp -rp app/ /var/www/html/vnstat/
$ cd /var/www/html/vnstat/
$ composer install
```

## Licensing
Copyright (C) 2019 Alexander Marston (alexander.marston@gmail.com)

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
