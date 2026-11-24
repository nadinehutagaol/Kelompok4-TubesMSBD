#!/bin/bash
set -e
# Pastikan password aplikasi sudah tersedia
: "${APP_DB_PASSWORD:?APP_DB_PASSWORD belum diatur}"
# Buat role aplikasi jika belum tersedia
psql -v ON_ERROR_STOP=1 --username "$POSTGRES_USER" --dbname "$POSTGRES_DB" <<'SQL'
DO $$
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM pg_roles WHERE rolname = 'trrrible_app'
    ) THEN
        CREATE ROLE trrrible_app LOGIN;
    END IF;
END
$$;
SQL

# Atur password role aplikasi dari environment variable
psql -v ON_ERROR_STOP=1 \
  --username "$POSTGRES_USER" \
  --dbname "$POSTGRES_DB" \
  -v app_password="$APP_DB_PASSWORD" <<'SQL'
SELECT format(
    'ALTER ROLE trrrible_app WITH LOGIN PASSWORD %L',
    :'app_password'
) \gexec
SQL

# The primary database is created by POSTGRES_DB=trrrible.
psql -v ON_ERROR_STOP=1 --username "$POSTGRES_USER" --dbname "$POSTGRES_DB" -f /postgresql/01_schema.sql
psql -v ON_ERROR_STOP=1 --username "$POSTGRES_USER" --dbname "$POSTGRES_DB" -f /postgresql/02_import_data.psql
psql -v ON_ERROR_STOP=1 --username "$POSTGRES_USER" --dbname "$POSTGRES_DB" -f /postgresql/03_sequences.sql
psql -v ON_ERROR_STOP=1 --username "$POSTGRES_USER" --dbname "$POSTGRES_DB" -f /postgresql/04_components.sql

# Berikan hak akses yang diperlukan aplikasi
psql -v ON_ERROR_STOP=1 --username "$POSTGRES_USER" --dbname "$POSTGRES_DB" <<'SQL'
GRANT CONNECT ON DATABASE trrrible TO trrrible_app;
GRANT USAGE ON SCHEMA public TO trrrible_app;

GRANT SELECT, INSERT, UPDATE, DELETE
ON ALL TABLES IN SCHEMA public
TO trrrible_app;

GRANT USAGE, SELECT
ON ALL SEQUENCES IN SCHEMA public
TO trrrible_app;


SQL

createdb --username "$POSTGRES_USER" trrrible_analytics
psql -v ON_ERROR_STOP=1 --username "$POSTGRES_USER" --dbname trrrible_analytics -f /postgresql/analytics_schema.sql

# Berikan akses baca ke database analytics
psql -v ON_ERROR_STOP=1 \
  --username "$POSTGRES_USER" \
  --dbname trrrible_analytics <<'SQL'

GRANT CONNECT ON DATABASE trrrible_analytics TO trrrible_app;
GRANT USAGE ON SCHEMA public TO trrrible_app;
GRANT SELECT ON ALL TABLES IN SCHEMA public TO trrrible_app;

SQL
