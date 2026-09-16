# Guia de Desenvolvimento

Este documento fornece instruções detalhadas para configurar o ambiente de desenvolvimento do SGE-IFFarSA.

## Pré-requisitos

Certifique-se de ter as seguintes ferramentas instaladas:

- Docker e Docker Compose (Essencial para rodar o Laravel Sail)
- Git

## Passo a Passo para Configuração

1. **Clone o repositório**:

   ```bash
   git clone https://github.com/estagiosa-cell/SGE-IFFarSA.git
   cd SGE-IFFarSA
   ```

2. **Configure o arquivo de ambiente**:

   Copie o arquivo de exemplo `.env.example` para `.env`:

   ```bash
   cp .env.example .env
   ```

   O arquivo `.env.example` já está configurado para utilizar PostgreSQL com o Laravel Sail. O SGE-IFFarSA usa recursos específicos do PostgreSQL; mantenha esse banco também no desenvolvimento e não troque para SQLite ou MySQL:

   ```env
   DB_CONNECTION=pgsql
   DB_HOST=pgsql
   DB_PORT=5432
   DB_DATABASE=sge
   DB_USERNAME=sail
   DB_PASSWORD=password
   ```

   **Serviços do Google**:
   Consulte o arquivo [GOOGLE_OAUTH_SETUP.md](GOOGLE_OAUTH_SETUP.md) para instruções detalhadas sobre a configuração da integração com as APIs do Google.

3. **Instale as dependências iniciais com Docker**:

   Como estamos usando o Laravel Sail, você pode instalar as dependências do Composer usando um pequeno container Docker antes de iniciar o Sail pela primeira vez:

   ```bash
   docker run --rm \
       -u "$(id -u):$(id -g)" \
       -v "$(pwd):/var/www/html" \
       -w /var/www/html \
       laravelsail/php82-composer:latest \
       composer install --ignore-platform-reqs
   ```

4. **Inicie o ambiente de desenvolvimento (Sail)**:

   ```bash
   ./vendor/bin/sail up -d
   ```

   > **Dica**: Você pode criar um alias no seu terminal para facilitar o uso do sail: `alias sail='bash vendor/bin/sail'`. Os comandos a seguir assumem o uso do `./vendor/bin/sail`.

5. **Gere a chave da aplicação**:

   ```bash
   ./vendor/bin/sail artisan key:generate
   ```

6. **Execute as Migrations**:

   ```bash
   ./vendor/bin/sail artisan migrate
   ```

7. **Instale as dependências do Node.js e compile os assets**:

   ```bash
   ./vendor/bin/sail npm install
   ./vendor/bin/sail npm run dev
   ```

   *(Mantenha este comando rodando em um terminal separado para compilar os assets durante o desenvolvimento)*.

8. **Configure o Envio de E-mails para Desenvolvimento (Mailpit)**:

   O ambiente Sail já inclui o Mailpit configurado por padrão. No seu `.env`, certifique-se de ter:

   ```env
   MAIL_MAILER=smtp
   MAIL_HOST=mailpit
   MAIL_PORT=1025
   ```
   A interface web do Mailpit pode ser acessada em `http://localhost:8025`.

## Acessando a Aplicação

A aplicação estará disponível em `http://localhost`.

Para rodar qualquer comando do artisan, npm ou composer, sempre prefixe com `./vendor/bin/sail`:

- **Artisan**: `./vendor/bin/sail artisan make:controller NomeController`
- **Composer**: `./vendor/bin/sail composer require pacote/exemplo`
- **NPM**: `./vendor/bin/sail npm install pacote`
