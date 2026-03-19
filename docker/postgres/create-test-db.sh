#!/bin/bash
# Runs once on first container start (docker-entrypoint-initdb.d).
# Creates the test database alongside the main one.
set -e

psql -v ON_ERROR_STOP=1 --username "$POSTGRES_USER" --dbname "$POSTGRES_DB" <<-EOSQL
    CREATE DATABASE gestiondeinvitaciones_test;
    GRANT ALL PRIVILEGES ON DATABASE gestiondeinvitaciones_test TO "$POSTGRES_USER";
EOSQL

echo "Test database 'gestiondeinvitaciones_test' created."
