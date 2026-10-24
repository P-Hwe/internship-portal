#!/usr/bin/env bash
# Roda a suíte completa com banco descartável. Uso: tests/run.sh [sqlite|mysql|pgsql]
set -e
cd "$(dirname "$0")/.."
DRV=${1:-sqlite}
export ADMIN_EMAIL=admin@ifsp.local ADMIN_PASSWORD='Admin@2026!' ADMIN_NOTIFY_EMAIL=admin@ifsp.local MAIL_DRIVER=log PRETTY_URLS=1 APP_URL=http://127.0.0.1:8123
if [ "$DRV" = sqlite ]; then export DB_SQLITE=/tmp/estagia-teste.sqlite; rm -f $DB_SQLITE; fi
rm -f storage/mail/*.html
php database/seed.php >/dev/null
php -S 127.0.0.1:8123 router.php >/tmp/estagia-srv1.log 2>&1 & P1=$!
SESSION_IDLE_ADMIN=2 php -S 127.0.0.1:8124 router.php >/tmp/estagia-srv2.log 2>&1 & P2=$!
trap "kill $P1 $P2 2>/dev/null" EXIT
sleep 1
php tests/fluxo.php http://127.0.0.1:8123 http://127.0.0.1:8124
php tests/migracao.php
