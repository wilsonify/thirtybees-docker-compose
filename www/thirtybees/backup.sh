#!/bin/bash
mariadb-dump --host=mariadb --user=$MARIADB_USER --password=MARIADB_PASSWORD thirtybees --xml > "mariadb_backup_$(date +'%Y%m%d%H%M%S').xml"
