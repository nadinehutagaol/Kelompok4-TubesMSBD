#!/usr/bin/env bash
# Fresh-machine setup. Does not delete existing databases or Docker volumes.
set -euo pipefail
cd "$(dirname "$0")"
for cmd in docker php composer npm; do
  command -v "$cmd" >/dev/null || { echo "Missing prerequisite: $cmd"; exit 1; }
done
if [[ ! -f .env ]]; then
  cp .env.example .env
  echo 'Created .env from .env.example. Configure APP_DB_PASSWORD and POSTGRES_SUPERUSER_PASSWORD, then run again.'
  exit 1
fi
if ! grep -Eq '^APP_DB_PASSWORD=.+$' .env || ! grep -Eq '^POSTGRES_SUPERUSER_PASSWORD=.+$' .env; then
  echo 'Set APP_DB_PASSWORD and POSTGRES_SUPERUSER_PASSWORD in .env first.'; exit 1
fi
# Start PostgreSQL before Laravel Composer scripts.
docker compose up -d --wait postgres

# Install dependencies after database is ready.
composer install --no-interaction

# Generate Laravel application key if needed.
php artisan key:generate --no-interaction

# Install and build frontend assets.
npm ci
npm run build
# Extract Compose-resolved credentials; do not print secrets.
compose_json="$(docker compose config --format json)"
admin_password="$(printf '%s' "$compose_json" | php -r '$j=json_decode(stream_get_contents(STDIN),true); echo $j["services"]["postgres"]["environment"]["POSTGRES_PASSWORD"] ?? "";')"
app_password="$(printf '%s' "$compose_json" | php -r '$j=json_decode(stream_get_contents(STDIN),true); echo $j["services"]["postgres"]["environment"]["APP_DB_PASSWORD"] ?? "";')"
admin_user="$(printf '%s' "$compose_json" | php -r '$j=json_decode(stream_get_contents(STDIN),true); echo $j["services"]["postgres"]["environment"]["POSTGRES_USER"] ?? "postgres";')"
forward_port="$(printf '%s' "$compose_json" | php -r '$j=json_decode(stream_get_contents(STDIN),true); $p=$j["services"]["postgres"]["ports"][0]["published"] ?? "5437"; echo $p;')"
if [[ -z "$admin_password" || -z "$app_password" ]]; then echo 'Missing Compose database credentials'; exit 1; fi
# Run migrations as database owner; app role must NOT own RLS-protected tables.
DB_CONNECTION=pgsql DB_HOST=127.0.0.1 DB_PORT="$forward_port" DB_DATABASE=trrrible DB_USERNAME="$admin_user" DB_PASSWORD="$admin_password" php artisan migrate --force
# Seed only missing demo products; does not modify stock, sales or existing previews.
DB_CONNECTION=pgsql DB_HOST=127.0.0.1 DB_PORT="$forward_port" DB_DATABASE=trrrible DB_USERNAME="$admin_user" DB_PASSWORD="$admin_password" php artisan db:seed --class=MarketplaceProductsSeeder --force
# Apply the existing, previously tested RLS SQL only when not yet installed.
policy_count="$(docker compose exec -T postgres psql -U "$admin_user" -d trrrible -Atqc "SELECT count(*) FROM pg_policies WHERE schemaname='public' AND tablename IN ('carts','cart_items','orders','order_items','payments','project_inquiries');")"
if [[ "$policy_count" == '0' ]]; then
  docker compose exec -T postgres psql -v ON_ERROR_STOP=1 -U "$admin_user" -d trrrible < database/postgresql/05_row_level_security.sql
elif [[ "$policy_count" != '10' ]]; then
  echo "Found $policy_count policies (expected 10). Stopping to avoid altering tested security rules."; exit 1
fi
# Grant newly migrated tables to the app role; preserve tested orders/payments restrictions.
docker compose exec -T postgres psql -v ON_ERROR_STOP=1 -U "$admin_user" -d trrrible <<'SQL'
GRANT CONNECT ON DATABASE trrrible TO trrrible_app;
GRANT USAGE ON SCHEMA public TO trrrible_app;
GRANT SELECT, INSERT, UPDATE, DELETE ON ALL TABLES IN SCHEMA public TO trrrible_app;
GRANT USAGE, SELECT ON ALL SEQUENCES IN SCHEMA public TO trrrible_app;
REVOKE UPDATE, DELETE ON public.orders, public.payments FROM trrrible_app;
SQL
# Local application credentials should match the role created by the bootstrap.
# Keep .env under the user's control; do not replace existing credentials automatically.
# Apply Marketplace previews after migrations and seeding.
php apply-demo-previews.php
php apply-remaining-svg-previews.php

php artisan storage:link || true
php artisan config:clear
printf '\nSetup complete. Ensure .env uses DB_USERNAME=trrrible_app, DB_PASSWORD=APP_DB_PASSWORD, DB_PORT=%s.\n' "$forward_port"
printf 'Then run: php artisan serve\n'
