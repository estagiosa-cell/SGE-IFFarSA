# Guia de Deploy - SGE-IFFarSA

Esta seção descreve os passos essenciais para implantar a aplicação em um servidor web, como uma VPS (Virtual Private Server), utilizando **Nginx**.

## Requisitos do Servidor

- **Servidor Web:** Nginx
- **PHP 8.2 ou superior** com as seguintes extensões: Ctype, cURL, DOM, Fileinfo, Filter, Hash, Mbstring, OpenSSL, PCRE, PDO, Session, Tokenizer, XML.
- **Banco de Dados:** MySQL, PostgreSQL, ou outro SGBD compatível.
- **Composer** (para gerenciamento de dependências PHP).
- **Node.js & NPM** (para compilar os assets do frontend).
- **Git** (para clonar o repositório).
- **Acesso SSH** ao servidor.

## Passos para Implantação (Deploy)

### 1. Clonar o Repositório no Servidor

Acesse seu servidor via SSH e clone o projeto para o diretório apropriado (ex: `/var/www/sge-iffarsa`).

```bash
git clone https://github.com/ArthurWillers/SGE-IFFarSA.git /var/www/sge-iffarsa
cd /var/www/sge-iffarsa
```

### 2. Instalar Dependências de Produção

Use o Composer com as flags de otimização para instalar apenas as dependências necessárias.

```bash
composer install --optimize-autoloader --no-dev
```

### 3. Configurar o Arquivo de Ambiente (`.env`)

Copie o arquivo de exemplo e preencha com as configurações do seu ambiente de produção.

```bash
cp .env.example .env
```

Edite o arquivo `.env` e configure as variáveis críticas:

- `APP_ENV=production`
- `APP_DEBUG=false`
- `APP_URL=https://seusite.com`
- Credenciais do banco de dados (`DB_*`) e e-mail (`MAIL_*`).
- Configurações das APIs do Google (consulte [GOOGLE_OAUTH_SETUP.md](GOOGLE_OAUTH_SETUP.md)).

### 4. Gerar a Chave da Aplicação

```bash
php artisan key:generate
```

### 5. Executar as Migrations

```bash
php artisan migrate --force
```

### 6. Executar os Seeders (Opcional)

Se você precisar popular o banco de dados com dados iniciais:

```bash
php artisan db:seed --force
```

### 7. Otimizar a Aplicação

Crie os arquivos de cache para máxima performance.

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 8. Compilar Assets de Produção

Instale as dependências e compile os arquivos CSS/JS para produção.

```bash
npm install
npm run build
```

### 9. Configurar Permissões de Diretório

Garanta que o Nginx tenha permissão de escrita nos diretórios `storage` e `bootstrap/cache`.

```bash
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

*(O usuário `www-data` pode variar dependendo do seu servidor).*

### 10. Configurar o Nginx

#### Arquivo de Configuração do Site (`.conf`)

Você precisará criar um arquivo de configuração de *server block* para o seu site em `/etc/nginx/sites-available/`. Ex: `/etc/nginx/sites-available/sge-iffarsa.conf`.

Este arquivo dirá ao Nginx como servir sua aplicação, apontando o `root` para a pasta `/public` e redirecionando as requisições para o PHP-FPM.

**Exemplo de configuração Nginx para Laravel:**

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name seusite.com www.seusite.com;
    root /var/www/sge-iffarsa/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

**Nota:** A **documentação oficial do Laravel** provê um excelente exemplo de configuração Nginx que deve ser usado como base. Você pode encontrá-lo na seção "Deployment".

#### Ativar o Site e Reiniciar o Nginx

Após criar e configurar o seu arquivo `.conf`, ative-o criando um link simbólico e reinicie o Nginx:

```bash
sudo ln -s /etc/nginx/sites-available/sge-iffarsa.conf /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx
```

## Atualizações Futuras

Para atualizar a aplicação após mudanças no código:

```bash
cd /var/www/sge-iffarsa
git pull origin main
composer install --optimize-autoloader --no-dev
npm install
npm run build
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
sudo systemctl restart nginx
```

## Segurança

- **Nunca** deixe `APP_DEBUG=true` em produção
- Mantenha o arquivo `.env` seguro e fora do controle de versão
- Configure um firewall (UFW) para permitir apenas as portas necessárias (80, 443, 22)
- Mantenha o PHP, Nginx e todas as dependências atualizadas
- Configure backups regulares do banco de dados e arquivos

---

Após esses passos, sua aplicação estará online e funcionando em modo de produção. 🚀