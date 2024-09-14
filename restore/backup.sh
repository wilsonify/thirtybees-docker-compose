#!/bin/bash
docker exec "thirtybees-docker-compose-mysql-1" mariadb-dump --all-databases --xml --user=$MYSQL_ROOT_USER --password=$MYSQL_ROOT_PASSWORD  > "mariadb_backup_$(date +'%Y%m%d%H%M%S').xml"
