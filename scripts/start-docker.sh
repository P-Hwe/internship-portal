#!/bin/bash

# Script para iniciar o MySQL com Docker e rodar migrações

echo "🐳 Iniciando MySQL com Docker..."

# Iniciar container MySQL
docker-compose up -d

echo ""
echo "⏳ Aguardando inicialização do MySQL..."
sleep 5

echo ""
echo "✅ MySQL iniciado!"
echo ""
echo "📊 Credenciais:"
echo "   Host: localhost"
echo "   Porta: 3306"
echo "   Usuário root: root / senha: root"
echo "   Usuário vagas: vagas / senha: vagas123"
echo "   Banco: vagas_dev"
echo ""
echo "📋 Para configurar no DBeaver:"
echo "   1. Arquivo → Novo Objeto de Dados → MySQL"
echo "   2. Host: localhost, Porta: 3306"
echo "   3. Usuário: root ou vagas"
echo "   4. Senha: root ou vagas123"
echo ""
echo "🚀 Executando migrações..."

# Rodar migrações
php database/migrate.php

echo ""
echo "✅ Tudo pronto! Abra o DBeaver e conecte-se."
echo ""
echo "📖 Leia INSTRUcoes_DBeaver.md para mais detalhes"
