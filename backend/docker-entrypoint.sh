#!/bin/sh
set -e

php artisan migrate --force

# Railway (e servicos similares) atribuem a porta publica via $PORT em runtime;
# localmente/Docker Compose, $PORT nao existe e cai no fallback 8000.
exec php artisan serve --host=0.0.0.0 --port="${PORT:-8000}"
