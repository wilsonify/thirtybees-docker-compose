#!/bin/bash
gzip -dk /var/www/default/1726346399-76b6d099.sql.gz
mariadb --host=mariadb --user=$MARIADB_USER --password=$MARIADB_PASSWORD thirtybees < $1

