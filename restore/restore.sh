#!/bin/bash
docker exec "thirtybees-docker-compose-mysql-1" mariadb --user=$MYSQL_ROOT_USER --password=$MYSQL_ROOT_PASSWORD --xml "thirtybees" < "mariadb_backup_20240914094546.xml"
