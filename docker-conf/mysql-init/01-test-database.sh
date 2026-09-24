#!/bin/bash
# Creates the test database next to the app's one, for the user the app connects as.
# The MySQL image runs this once, when the database volume is first created. For an existing
# volume, run the same two statements as root, or recreate the volume: docker compose down -v
set -e

mysql --protocol=socket -uroot -p"$MYSQL_ROOT_PASSWORD" <<SQL
CREATE DATABASE IF NOT EXISTS \`${MYSQL_DATABASE}_test\`;
GRANT ALL PRIVILEGES ON \`${MYSQL_DATABASE}_test\`.* TO '${MYSQL_USER}'@'%';
SQL
