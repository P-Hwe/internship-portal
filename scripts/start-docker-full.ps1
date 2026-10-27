# Script Completo para Iniciar Ambiente de Teste
# Uso: .\scripts\start-docker-full.ps1

Write-Host ""
Write-Host "==================================================" -ForegroundColor Cyan
Write-Host "  🚀 START DOCKER FULL - Ambiente de Teste" -ForegroundColor Cyan
Write-Host "==================================================" -ForegroundColor Cyan
Write-Host ""

# Passo 1: Verificar Docker
Write-Host "[1/5] Verificando Docker..." -ForegroundColor Yellow
try {
    $dockerVersion = docker --version
    Write-Host "   ✅ Docker instalado: $dockerVersion" -ForegroundColor Green
} catch {
    Write-Host "   ❌ Docker não encontrado!" -ForegroundColor Red
    Write-Host "   Por favor, instale o Docker Desktop primeiro." -ForegroundColor Red
    exit 1
}

# Passo 2: Iniciar Containers
Write-Host ""
Write-Host "[2/5] Iniciando containers Docker..." -ForegroundColor Yellow
try {
    docker-compose up -d
    Write-Host "   ✅ Containers iniciados!" -ForegroundColor Green

    # Aguardar inicialização
    Write-Host "   ⏳ Aguardando MySQL iniciar..." -ForegroundColor Yellow
    Start-Sleep -Seconds 10
} catch {
    Write-Host "   ❌ Erro ao iniciar containers: $_" -ForegroundColor Red
    exit 1
}

# Passo 3: Verificar Status
Write-Host ""
Write-Host "[3/5] Verificando status..." -ForegroundColor Yellow
docker-compose ps | ForEach-Object {
    if ($_ -match "Up") {
        Write-Host "   ✅ $($_.Split(' ')[0])" -ForegroundColor Green
    } else {
        Write-Host "   ⚠️  $($_.Split(' ')[0])" -ForegroundColor Yellow
    }
}

# Passo 4: Configurar Banco de Dados
Write-Host ""
Write-Host "[4/5] Configurando banco de dados..." -ForegroundColor Yellow

# Criar banco se necessário
docker-compose exec mysql-vagas mysql -uroot -proot -e "CREATE DATABASE IF NOT EXISTS vagas;" 2>$null
if ($LASTEXITCODE -eq 0) {
    Write-Host "   ✅ Banco criado/atualizado" -ForegroundColor Green
}

# Rodar seed
Write-Host ""
Write-Host "[5/5] Executando seed..." -ForegroundColor Yellow
php database\seed.php

# Passo 5: Mostrar Credenciais
Write-Host ""
Write-Host "==================================================" -ForegroundColor Cyan
Write-Host "  ✅ AMBIENTE PRONTO!" -ForegroundColor Green
Write-Host "==================================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "📊 Acesso ao Banco de Dados:" -ForegroundColor Cyan
Write-Host "   Host: localhost" -ForegroundColor White
Write-Host "   Porta: 3306" -ForegroundColor White
Write-Host "   Banco: vagas" -ForegroundColor White
Write-Host "   Usuário: root / senha: root" -ForegroundColor White
Write-Host ""
Write-Host "🌐 Acesso ao Portal:" -ForegroundColor Cyan
Write-Host "   http://localhost:8000" -ForegroundColor White
Write-Host ""
Write-Host "📖 Para configurar no DBeaver:" -ForegroundColor Cyan
Write-Host "   1. Arquivo → Novo Objeto de Dados → MySQL" -ForegroundColor White
Write-Host "   2. Host: localhost, Porta: 3306" -ForegroundColor White
Write-Host "   3. Banco: vagas, Usuário: root, Senha: root" -ForegroundColor White
Write-Host ""
Write-Host "📖 Leia GUIA_TESTE.md para mais detalhes" -ForegroundColor Cyan
Write-Host ""
