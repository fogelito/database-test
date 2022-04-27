# docker run --name mariadbtest -e MYSQL_ROOT_PASSWORD=mypass -p 33061:3306  -d docker.io/library/mariadb:10.7.3

# docker run --rm -p 8000:8000 -v $(pwd):/app -w /app custom-php-image php -S 0.0.0.0:8000 server.php

FROM php:8.1-cli

RUN docker-php-ext-install pdo pdo_mysql
