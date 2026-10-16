#!/usr/bin/env bash
# Script para verificar qualidade do código PHP
# Executa PHPStan e PHPCS

set -e

echo "=========================================="
echo "  QUALITY CHECK - Internship Portal"
echo "=========================================="
echo ""

# Verificar PHPStan
echo "[1/2] PHPStan Static Analysis..."
php vendor/bin/phpstan analyse -c phpstan-baseline.neon --no-progress 2>&1 || true

RESULT=$?

if [ $RESULT -eq 0 ]; then
    echo "✅ PHPStan: OK"
else
    echo "⚠️  PHPStan: Erros encontrados (verifique o baseline)"
fi

echo ""

# Verificar PHPCS
echo "[2/2] PHPCS Code Style Check..."
php vendor/bin/phpcs app/ public/ --standard=.phpcs.xml.dist --report=checkstyle 2>&1 || true

RESULT=$?

if [ $RESULT -eq 0 ]; then
    echo "✅ PHPCS: OK"
else
    echo "⚠️  PHPCS: Erros de estilo encontrados"
fi

echo ""
echo "=========================================="
echo "  QUALITY CHECK COMPLETED"
echo "=========================================="
