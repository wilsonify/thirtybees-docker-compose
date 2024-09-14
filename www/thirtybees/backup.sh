#!/bin/bash
mariadb-dump --host=mysql --user=$MYSQL_ROOT_USER --password=$MYSQL_ROOT_PASSWORD thirtybees --xml > "mariadb_backup_$(date +'%Y%m%d%H%M%S').xml"
