#!/bin/bash

set -e

# Cores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# URL da aplicação (pode ser configurada)
APP_URL="${APP_URL:-http://localhost}"

echo -e "${BLUE}=========================================="
echo "SGE-IFFarSA - Script de Atualização"
echo -e "==========================================${NC}"

# Verificar se está rodando como root/sudo
if [ "$EUID" -ne 0 ]; then
    echo -e "${RED}❌ Erro: Este script precisa ser executado com sudo!${NC}"
    echo -e "${YELLOW}   Execute: sudo bash $0${NC}"
    exit 1
fi

# Obter o usuário real (não root)
REAL_USER=${SUDO_USER:-$USER}
if [ "$REAL_USER" = "root" ]; then
    echo -e "${RED}❌ Erro: Não execute este script diretamente como root!${NC}"
    echo -e "${YELLOW}   Execute com sudo a partir de um usuário normal.${NC}"
    exit 1
fi

echo -e "${BLUE}ℹ️  Executando como: root (via sudo)${NC}"
echo -e "${BLUE}ℹ️  Usuário original: $REAL_USER${NC}"

# Verificar se está na pasta correta
if [ ! -f "artisan" ]; then
    echo -e "${RED}❌ Erro: Execute este script na raiz do projeto Laravel!${NC}"
    exit 1
fi

# Verificar se o arquivo .env existe
if [ ! -f ".env" ]; then
    echo -e "${RED}❌ Erro: Arquivo .env não encontrado!${NC}"
    exit 1
fi

# Verificar se comandos necessários estão disponíveis
echo -e "\n${BLUE}🔍 Verificando dependências...${NC}"
REQUIRED_COMMANDS=("git" "php" "composer" "npm" "curl")
for cmd in "${REQUIRED_COMMANDS[@]}"; do
    if ! command -v $cmd &> /dev/null; then
        echo -e "${RED}❌ Erro: $cmd não está instalado!${NC}"
        exit 1
    fi
done
echo -e "${GREEN}✓ Todas as dependências estão instaladas!${NC}"

# Verificar conexão com repositório Git
echo -e "\n${BLUE}🔍 Verificando conexão com repositório Git...${NC}"
if ! su - $REAL_USER -c "cd $PWD && git ls-remote" &> /dev/null; then
    echo -e "${RED}❌ Erro: Não foi possível conectar ao repositório Git!${NC}"
    echo -e "${YELLOW}   Verifique sua conexão de rede e permissões.${NC}"
    exit 1
fi
echo -e "${GREEN}✓ Conexão com repositório OK!${NC}"

# Verificar se há mudanças não commitadas (como usuário normal)
echo -e "\n${BLUE}🔍 Verificando estado do repositório...${NC}"
if ! su - $REAL_USER -c "cd $PWD && git diff-index --quiet HEAD --" 2>/dev/null; then
    echo -e "${YELLOW}⚠️  Detectadas mudanças locais não commitadas.${NC}"
    echo -e "${YELLOW}   As mudanças locais serão descartadas durante a atualização.${NC}"
fi

# Colocar aplicação em modo de manutenção
echo -e "\n${YELLOW}⏸️  Colocando aplicação em modo de manutenção...${NC}"
php artisan down

# Função para restaurar a aplicação em caso de erro
restore_app() {
    echo -e "\n${RED}❌ Erro detectado! Restaurando aplicação...${NC}"
    php artisan up
    exit 1
}

# Capturar erros e restaurar aplicação
trap restore_app ERR

# Atualizar código do repositório (como usuário normal)
echo -e "\n${BLUE}📥 Atualizando código do repositório...${NC}"
su - $REAL_USER -c "cd $PWD && git fetch origin"
CURRENT_BRANCH=$(su - $REAL_USER -c "cd $PWD && git rev-parse --abbrev-ref HEAD")
echo -e "${BLUE}Branch atual: ${CURRENT_BRANCH}${NC}"

# Descartar mudanças locais e atualizar (como root para ter permissão)
echo -e "${BLUE}Descartando mudanças locais...${NC}"
git reset --hard origin/$CURRENT_BRANCH

if [ $? -eq 0 ]; then
    echo -e "${GREEN}✓ Código atualizado com sucesso!${NC}"
else
    echo -e "${RED}❌ Erro ao atualizar código!${NC}"
    restore_app
fi

# Atualizar dependências do Composer
echo -e "\n${BLUE}📦 Instalando dependências do Composer...${NC}"
if composer install --no-interaction --optimize-autoloader --no-dev --no-scripts; then
    echo -e "${GREEN}✓ Dependências do Composer instaladas!${NC}"
else
    echo -e "${RED}❌ Erro ao instalar dependências do Composer!${NC}"
    restore_app
fi

# Atualizar dependências do NPM
echo -e "\n${BLUE}📦 Instalando dependências do NPM...${NC}"
if npm install; then
    echo -e "${GREEN}✓ Dependências do NPM instaladas!${NC}"
else
    echo -e "${RED}❌ Erro ao instalar dependências do NPM!${NC}"
    restore_app
fi

# Compilar assets
echo -e "\n${BLUE}🔨 Compilando assets...${NC}"
if npm run build; then
    echo -e "${GREEN}✓ Assets compilados!${NC}"
else
    echo -e "${RED}❌ Erro ao compilar assets!${NC}"
    restore_app
fi

# Executar migrações do banco de dados
echo -e "\n${BLUE}🗄️  Executando migrações do banco de dados...${NC}"
if php artisan migrate --force; then
    echo -e "${GREEN}✓ Migrações executadas!${NC}"
else
    echo -e "${RED}❌ Erro ao executar migrações!${NC}"
    restore_app
fi

# Otimizar aplicação
echo -e "\n${BLUE}⚡ Otimizando aplicação...${NC}"
if php artisan optimize; then
    echo -e "${GREEN}✓ Aplicação otimizada!${NC}"
else
    echo -e "${RED}❌ Erro ao otimizar aplicação!${NC}"
    restore_app
fi

# Definir permissões corretas
echo -e "\n${BLUE}🔐 Definindo permissões corretas...${NC}"
if chown -R www-data:www-data storage bootstrap/cache 2>/dev/null && \
   chmod -R 775 storage bootstrap/cache 2>/dev/null; then
    echo -e "${GREEN}✓ Permissões definidas!${NC}"
else
    echo -e "${YELLOW}⚠️  Aviso: Não foi possível definir algumas permissões!${NC}"
    echo -e "${YELLOW}   Isso pode ser normal em alguns ambientes.${NC}"
fi

# Verificar permissões de escrita em diretórios críticos
echo -e "\n${BLUE}🔍 Verificando permissões de escrita...${NC}"
CRITICAL_DIRS=("storage/logs" "storage/framework/cache" "storage/framework/sessions" "storage/framework/views" "bootstrap/cache")
for dir in "${CRITICAL_DIRS[@]}"; do
    if [ ! -w "$dir" ]; then
        echo -e "${YELLOW}⚠️  Aviso: Sem permissão de escrita em $dir${NC}"
    fi
done
echo -e "${GREEN}✓ Verificação de permissões concluída!${NC}"

# Retirar aplicação do modo de manutenção
echo -e "\n${YELLOW}▶️  Retirando aplicação do modo de manutenção...${NC}"
php artisan up
echo -e "${GREEN}✓ Aplicação ativa!${NC}"

# Verificar se a aplicação está funcionando
echo -e "\n${BLUE}🔍 Verificando se a aplicação está respondendo...${NC}"
sleep 2

HTTP_STATUS=$(curl -s -o /dev/null -w "%{http_code}" "$APP_URL/up" || echo "000")

if [ "$HTTP_STATUS" = "200" ]; then
    echo -e "${GREEN}✓ Aplicação está respondendo corretamente! (HTTP $HTTP_STATUS)${NC}"
elif [ "$HTTP_STATUS" = "000" ]; then
    echo -e "${YELLOW}⚠️  Não foi possível verificar a aplicação em $APP_URL/up${NC}"
    echo -e "${YELLOW}   Verifique manualmente se a aplicação está funcionando.${NC}"
else
    echo -e "${YELLOW}⚠️  Aplicação retornou status HTTP $HTTP_STATUS${NC}"
    echo -e "${YELLOW}   Verifique os logs para mais detalhes.${NC}"
fi

# Verificar se há erros nos logs
echo -e "\n${BLUE}🔍 Verificando erros recentes nos logs...${NC}"
if [ -f "storage/logs/laravel.log" ]; then
    RECENT_ERRORS=$(tail -n 100 storage/logs/laravel.log | grep -i "error" | wc -l)
    if [ "$RECENT_ERRORS" -gt 0 ]; then
        echo -e "${YELLOW}⚠️  Encontrados $RECENT_ERRORS erros recentes no log!${NC}"
        echo -e "${YELLOW}   Verifique storage/logs/laravel.log para mais detalhes.${NC}"
    else
        echo -e "${GREEN}✓ Nenhum erro recente detectado nos logs!${NC}"
    fi
else
    echo -e "${BLUE}ℹ️  Arquivo de log ainda não foi criado.${NC}"
fi

echo -e "\n${GREEN}=========================================="
echo "✅ Atualização concluída com sucesso!"
echo -e "==========================================${NC}"