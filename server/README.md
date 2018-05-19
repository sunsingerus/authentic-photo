[![Gitter](https://img.shields.io/gitter/room/nwjs/nw.js.svg)](https://gitter.im/bootstrapi/Lobby)
[![Build Status](https://travis-ci.org/kot13/bootstrapi.svg?branch=master)](https://travis-ci.org/kot13/bootstrapi)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/kot13/bootstrapi/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/kot13/bootstrapi/?branch=master)
[![Total Downloads](https://poser.pugx.org/pmurkin/bootstrapi/downloads)](https://packagist.org/packages/pmurkin/bootstrapi)
[![License](https://poser.pugx.org/pmurkin/bootstrapi/license)](https://packagist.org/packages/pmurkin/bootstrapi)

# bootstrapi
A better framework for building API with PHP. Built using Slim 3, Eloquent, Zend-ACL

## Feature
* JWT authentication
* Validation request
* ACL role based
* Support base CRUD operation
* Filtering && Sorting && Pagination
* DB migration
* CLI-tools
* JSONAPI negotiation
* Generated documentation
* Code generator commands
* Log

## Inside:
* Slim3 http://www.slimframework.com/
* ORM Eloquent https://laravel.com/docs/5.5/eloquent
* Zend ACL https://zendframework.github.io/zend-permissions-acl/
* JsonApi https://github.com/neomerx/json-api
* JWT https://github.com/firebase/php-jwt
* SwiftMailer http://swiftmailer.org/
* ApiDocJS http://apidocjs.com/
* PHP dotenv https://github.com/vlucas/phpdotenv

## Demo
[Example documentation](http://docs.bootstrapi.demostage.ru/)

[Example client (Ember.js application)](http://bootstrapi.demostage.ru/)

[Client repo](https://github.com/kot13/bootstrapi-client)

## Requirements
* PHP >= 5.6
* Composer
* Nginx / Apache
* MySQL / PostgreSQL
* NodeJs && NPM && ApiDocJs (for docs generate)

## Donate
Bitcoin payments: 1LLw4WuBz1oUSjQFntfQutD2T8mGZiA7pZ

## Installing
1) create new project
```
$ composer create-project -n -s dev pmurkin/bootstrapi my-api
```

2) change config files:
```
$ nano .env
$ nano config/apidoc.php
$ nano version.sh
```

3) configure server

Example configuration for nginx:
```
server {
    listen 80 ;
    server_name     hostname;
    error_log       /path/to/nginx/logs/hostname.error.log;
    access_log      /path/to/nginx/logs/hostname.access.log;
    index           /frontend/index.html index.html;

    root   /path/to/projects/hostname;

    location ~* (.+\.(js|css|png|jpg|gif|swf|ico|pdf|mov|fla|zip|rar|woff|woff2|ttf|eot|svg))$ {
        root   /path/to/projects/hostname/frontend;
        try_files       $uri =404;
    }

    location ~ /api/ {
        if (!-e $request_filename) {rewrite ^/(.*)$ /public/index.php?q=$1 last;}
    }

    location ~ \.php$ {
        try_files $uri =404;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root/$fastcgi_script_name;
        fastcgi_pass   127.0.0.1:9000;
    }

    location / {
        if (!-e $request_filename) {rewrite ^/(.*)$ /frontend/index.html?q=$1 last;}
    }
}

server {
    listen 80 ;
    server_name     docs.hostname;
    error_log       /path/to/nginx/logs/hostname.error.log;
    access_log      /path/to/nginx/logs/hostname.access.log;
    index           index.html;
    root            /path/to/projects/hostname/docs;

    location / {
        try_files $uri $uri/ /index.html?$args;
    }

    location ~* (.+\.(js|css|png|jpg|gif|swf|ico|pdf|mov|fla|zip|rar|woff|woff2|ttf|eot|svg))$ {
        try_files $uri =404;
    }
}

server {
    listen 80 ;
    server_name     static.hostname;
    error_log       /path/to/nginx/logs/hostname.error.log;
    access_log      /path/to/nginx/logs/hostname.access.log;

    root            /path/to/projects/hostname/public/uploads;

    location ~* (.+\.(js|css|png|jpg|gif|swf|ico|pdf|mov|fla|zip|rar|woff|woff2|ttf|eot|svg))$ {
        add_header 'Access-Control-Allow-Origin' '*';
        add_header 'Access-Control-Allow-Credentials' 'true';
        add_header 'Access-Control-Allow-Methods' 'GET, POST, PUT, PATCH, DELETE, OPTIONS';
        add_header 'Access-Control-Allow-Headers' 'Authorization,Content-Type,Accept,Origin,User-Agent,DNT,Cache-Cont$
        try_files $uri =404;
    }
}
```

Example configuration for apache:
```
<VirtualHost *:80>
   ServerName hostname
   DocumentRoot "/path/to/projects/hostname/"

   <Directory "/path/to/projects/hostname/public/">
       # use mod_env for define environment variables
       SetEnv APPLICATION_ENV develop
       SetEnv SECRET_KEY mysecretkey
   
       # use mod_rewrite for pretty URL support
       RewriteEngine on
       # If a directory or a file exists, use the request directly
       RewriteCond %{REQUEST_FILENAME} !-f
       RewriteCond %{REQUEST_FILENAME} !-d
       # Otherwise forward the request to index.php
       RewriteRule ^api/(.*)$ /index.php?q=$1 [L]

       # use index.php as index file
       DirectoryIndex index.php

       # ...other settings...
       # Apache 2.4
       Require all granted

       ## Apache 2.2
       # Order allow,deny
       # Allow from all
   </Directory>
</VirtualHost>

<VirtualHost *:80>
   ServerName docs.hostname
   DocumentRoot "/path/to/projects/hostname/docs"

   <Directory "/path/to/projects/hostname/docs">
       # use index.html as index file
       DirectoryIndex index.html

       # ...other settings...
       # Apache 2.4
       Require all granted

       ## Apache 2.2
       # Order allow,deny
       # Allow from all
   </Directory>
</VirtualHost>
```

4) migration
```
$ php partisan migrate:up
```

5) migration
```
$ php partisan run:seed
```

6) generate documentation (optional)
```
$ php partisan generate:docs
```
