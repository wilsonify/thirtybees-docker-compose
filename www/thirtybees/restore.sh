#!/bin/bash
docker exec thirtybees-docker-compose-mysql-1 gzip -dk /var/www/default/1726346399-76b6d099.sql.gz
docker exec thirtybees-docker-compose-mysql-1 mariadb --host=mysql --user=$MYSQL_USER --password=$MYSQL_PASSWORD thirtybees < /var/www/default/1726346399-76b6d099.sql

