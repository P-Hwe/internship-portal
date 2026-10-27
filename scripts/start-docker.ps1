# Script PowerShell para iniciar MySQL com Docker

Write-Host "🐳 Iniciando MySQL com Docker..." -ForegroundColor Cyan

# Iniciar container MySQL
docker-compose up -d

Write-Host ""
Write-Host "⏳ Aguardando inicialização do MySQL..." -ForegroundColor Yellow
Start-Sleep -Seconds 5

Write-Host ""
Write-Host "✅ MySQL iniciado!" -ForegroundColor Green
Write-Host ""
Write-Host "📊 Credenciais:" -ForegroundColor Cyan
Write-Host "   Host: localhost"
Write-Host "   Porta: 3306"
Write-Host "   Usuário root: root / senha: root"
Write-Host "   Usuário vagas: vagas / senha: vagas123"
Write-Host "   Banco: vagas_dev"
Write-Host ""
Write-Host "📋 Para configurar no DBeaver:" -ForegroundColor Cyan
Write-Host "   1. Arquivo → Novo Objeto de Dados → MySQL"
Write-Host "   2. Host: localhost, Porta: 3306"
Write-Host "   3. Usuário: root ou vagas"
Write-Host "   4. Senha: root ou vagas123"
Write-Host ""
Write-Host "🚀 Executando migrações..." -ForegroundColor Cyan

# Rodar migrações
php database/migrate.php

Write-Host ""
Write-Host "✅ Tudo pronto! Abra o DBeaver e conecte-se." -ForegroundColor Green
Write-Host ""
Write-Host "📖 Leia INSTRUcoes_DBeaver.md para mais detalhes" -ForegroundColor Yellow
