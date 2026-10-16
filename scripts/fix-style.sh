#!/usr/bin/env bash

set -e

echo "=========================================="
echo "  FIX STYLE - PHPCBF"
echo "=========================================="
echo ""

# Corrigir automaticamente os erros de estilo
echo "Corrigindo erros de estilo automaticamente..."
php vendor/bin/phpcbf app/ public/ --standard=.phpcs.xml.dist --report=checkstyle 2>&1 || true

RESULT=$?

if [ $RESULT -eq 0 ]; then
    echo ""
    echo "✅ Erros de estilo corrigidos!"
else
    echo ""
    echo "⚠️  Alguns erros não puderam ser corrigidos automaticamente"
fi

echo ""
echo "=========================================="
echo "  STYLE FIX COMPLETED"
echo "=========================================="
