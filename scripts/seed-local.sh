#!/bin/bash
# 🔒 seed-local.sh - Executa seed.php apenas localmente em ambiente dev

# Verifica ambiente
if [ "$APP_ENV" != "dev" ] && [ "$APP_ENV" != "local" ]; then
    echo "❌ Seed só pode rodar em APP_ENV=dev ou local"
    exit 1
fi

# Verifica se está no localhost
if [ "$REMOTE_ADDR" != "127.0.0.1" ] && [ "$REMOTE_ADDR" != "::1" ]; then
    echo "❌ Seed só pode rodar em localhost"
    exit 1
fi

echo "📦 Iniciando seed.php..."
php database/seed.php --force
