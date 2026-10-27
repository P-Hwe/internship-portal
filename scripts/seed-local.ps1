# Script de Seed Local - Preenche o banco com dados de placeholder
# Uso: .\scripts\seed-local.ps1

Write-Host ""
Write-Host "==================================================" -ForegroundColor Cyan
Write-Host "  🌱 SEED LOCAL - Preenchendo Banco de Dados" -ForegroundColor Cyan
Write-Host "==================================================" -ForegroundColor Cyan
Write-Host ""

# Verificar se o Docker está rodando
Write-Host "🔍 Verificando Docker..." -ForegroundColor Yellow
$dockerRunning = docker ps | Select-String "mysql-vagas" -SimpleMatch
if (-not $dockerRunning) {
    Write-Host "❌ MySQL não está rodando. Iniciando..." -ForegroundColor Red
    docker-compose up -d
    Start-Sleep -Seconds 5
}

# Verificar se o banco está pronto
Write-Host ""
Write-Host "📊 Verificando status do banco..." -ForegroundColor Yellow
docker-compose exec mysql-vagas mysql -uroot -proot -e "SHOW DATABASES;" | Select-String "vagas" | Out-Null
if ($LASTEXITCODE -eq 0) {
    Write-Host "✅ Banco 'vagas' existe" -ForegroundColor Green
} else {
    Write-Host "⚠️  Banco 'vagas' não existe, criando..." -ForegroundColor Yellow
}

# Criar banco se necessário
Write-Host ""
Write-Host "📦 Criando banco de dados..." -ForegroundColor Yellow
docker-compose exec mysql-vagas mysql -uroot -proot -e "CREATE DATABASE IF NOT EXISTS vagas;"

# Rodar seed
Write-Host ""
Write-Host "🌱 Executando seed..." -ForegroundColor Yellow
php database\seed.php

# Verificar se o seed foi bem-sucedido
Write-Host ""
Write-Host "📋 Verificando tabelas criadas..." -ForegroundColor Yellow
docker-compose exec mysql-vagas mysql -uroot -pvagas -e "SHOW TABLES FROM vagas;" 2>$null | Where-Object { $_ -notmatch "Password" }

# Ler configurações do .env
Write-Host ""
Write-Host "👤 Configurações de Admin:" -ForegroundColor Yellow
$adminEmail = (Get-Content .env | Select-String "^ADMIN_EMAIL=").ToString().Split('=', 2)[1].Trim()
$adminPass = (Get-Content .env | Select-String "^ADMIN_PASSWORD=").ToString().Split('=', 2)[1].Trim()

if ([string]::IsNullOrWhiteSpace($adminEmail)) {
    $adminEmail = "teste@seudominio.edu.br"
}

if ([string]::IsNullOrWhiteSpace($adminPass)) {
    $adminPass = "TroqueEstaSenha123"
}

Write-Host "   Email: $adminEmail" -ForegroundColor Green
Write-Host "   Senha: $adminPass" -ForegroundColor Green

# Rodar seed de admin se necessário
Write-Host ""
Write-Host "🔐 Verificando admin..." -ForegroundColor Yellow
if (Test-Path "database\seed-admin.php") {
    & php database\seed-admin.php "$adminEmail" "$adminPass" 2>$null
} else {
    Write-Host "   ⚠️  Script seed-admin não encontrado, pulando..." -ForegroundColor Yellow
}

Write-Host ""
Write-Host "==================================================" -ForegroundColor Cyan
Write-Host "  ✅ SEED CONCLUÍDO!" -ForegroundColor Green
Write-Host "==================================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "📊 Acesso ao banco:" -ForegroundColor Cyan
Write-Host "   Host: localhost" -ForegroundColor White
Write-Host "   Porta: 3306" -ForegroundColor White
Write-Host "   Banco: vagas" -ForegroundColor White
Write-Host "   Usuário: root / senha: root (ou use DBeaver)" -ForegroundColor White
Write-Host ""
Write-Host "🌐 Acesso ao portal:" -ForegroundColor Cyan
Write-Host "   http://localhost:8000" -ForegroundColor White
Write-Host ""
Write-Host "📖 Leia GUIA_TESTE.md para mais detalhes" -ForegroundColor Cyan
Write-Host ""
