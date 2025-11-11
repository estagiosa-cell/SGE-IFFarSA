# Guia de Desenvolvimento

Este documento fornece instruções detalhadas para configurar o ambiente de desenvolvimento do SGE-IFFarSA.

## Pré-requisitos

Certifique-se de ter as seguintes ferramentas instaladas:

- PHP >= 8.2
- Composer
- Node.js & NPM

## Passo a Passo para Configuração

1. **Clone o repositório**:

   ```bash
   git clone https://github.com/estagiosa-cell/SGE-IFFarSA.git
   cd SGE-IFFarSA
   ```

2. **Instale as dependências do PHP**:

   ```bash
   composer install
   ```

3. **Instale as dependências do Node.js**:

   ```bash
   npm install
   ```

4. **Configure o arquivo de ambiente**:

   Copie o arquivo de exemplo `.env.example` para `.env`:

   ```bash
   cp .env.example .env
   ```

   Atualize as variáveis de ambiente conforme necessário:

   - **Configurações gerais da aplicação**:
     ```env
     APP_NAME=SGE-IFFarSA
     APP_ENV=local
     APP_DEBUG=true
     APP_URL=http://localhost:8000
     APP_LOCALE=pt_BR
     APP_TIMEZONE=America/Sao_Paulo
     ```

   - **Serviços do Google**:
     Consulte o arquivo [GOOGLE_OAUTH_SETUP.md](GOOGLE_OAUTH_SETUP.md) para instruções detalhadas sobre a configuração da integração com as APIs do Google.
     ```env
     GOOGLE_CLIENT_ID=seu_client_id_google
     GOOGLE_CLIENT_SECRET=seu_client_secret_google
     GOOGLE_REDIRECT_URI=http://localhost:8000/google/callback
     GOOGLE_ADMIN_ACCOUNT_EMAIL=admin@exemplo.com
     GOOGLE_SHEET_ID_DATA_COLLECTION=<id_da_sua_planilha_de_coleta>
     GOOGLE_SHEET_ID_SUPERVISOR_EVALUATION=<id_da_planilha_de_avaliacoes>
     GOOGLE_DRIVE_FOLDER_ID=<id_da_pasta_no_drive>
     GOOGLE_DOCS_TEMPLATE_ID_TERMO_COMPROMISSO_PADRAO=<id_do_template_padrao>
     GOOGLE_DOCS_TEMPLATE_ID_TERMO_EMATER_RS=<id_do_template_emater_rs>
     GOOGLE_DOCS_TEMPLATE_ID_TERMO_SEDUC=<id_do_template_seduc>
     GOOGLE_DOCS_TEMPLATE_ID_RESCISAO=<id_do_template_rescisao>
     GOOGLE_DOCS_TEMPLATE_ID_CREDENCIAMENTO=<id_do_template_credenciamento>
     ```

5. **Gere a chave da aplicação**:

   ```bash
   php artisan key:generate
   ```

6. **Configure o Banco de Dados para Desenvolvimento**:

   No arquivo `.env`, configure para usar SQLite:

   ```env
   DB_CONNECTION=sqlite
   DB_DATABASE=database/database.sqlite
   ```

   > **Nota**: O Laravel criará automaticamente o arquivo `database/database.sqlite` quando executar as migrations.

7. **Execute as Migrations**:

   ```bash
   php artisan migrate
   ```

8. **Configure o Envio de E-mails para Desenvolvimento**:

   Configure um servidor SMTP no arquivo `.env`. Para desenvolvimento, Mailtrap.io é recomendado:

   ```env
   MAIL_MAILER=smtp
   MAIL_HOST=sandbox.smtp.mailtrap.io
   MAIL_PORT=2525
   MAIL_USERNAME=seu_usuario_mailtrap
   MAIL_PASSWORD=sua_senha_mailtrap
   MAIL_FROM_ADDRESS="email@exemplo.com"
   MAIL_FROM_NAME="${APP_NAME}"
   ```
   Alternativamente, você pode usar o driver `log` para registrar os e-mails em um arquivo de log, sem enviá-los.

   > **Nota**: Ao usar o driver `log`, os e-mails serão registrados no arquivo de log configurado no Laravel, geralmente localizado em `storage/logs/laravel.log`.

## Executando a Aplicação

Para iniciar o ambiente de desenvolvimento, utilize o script `dev` configurado no `composer.json`, que inicia o servidor do Laravel, o Vite e outras ferramentas simultaneamente:

```bash
composer run dev
```

Ou, se preferir, inicie os processos separadamente em terminais diferentes:

1. **Inicie o servidor de desenvolvimento do Laravel**:

   ```bash
   php artisan serve
   ```

2. **Compile os assets do frontend e observe as mudanças**:

   ```bash
   npm run dev
   ```

A aplicação estará disponível em `http://localhost:8000`.