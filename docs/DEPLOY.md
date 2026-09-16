# Guia de deploy — SGE-IFFarSA

Este guia descreve uma implantação de produção em uma VPS Linux usando Nginx, PHP-FPM e PostgreSQL. Ele foi escrito com base na configuração atual do projeto.

> **Escopo do sistema:** o SGE-IFFarSA foi pensado para uso em uma intranet institucional. A aplicação não é um portal público independente: ela depende dos formulários, planilhas, documentos e da pasta do Google Workspace da instituição para coletar dados, sincronizar estágios e gerar documentos.
>
> **PostgreSQL é obrigatório.** Não use SQLite ou MySQL em produção. O projeto usa PostgreSQL diretamente, inclusive as extensões `unaccent` e `pg_trgm` e colunas `jsonb` criadas pelas migrations.
>
> Mesmo em uma intranet, o servidor precisa ter saída HTTPS para as APIs do Google e para o SMTP. O navegador do administrador também precisa conseguir abrir a autorização OAuth e retornar para o domínio cadastrado no Google Cloud.

## Visão geral da arquitetura

Em produção, a arquitetura recomendada é:

```text
Internet → HTTPS/Nginx → PHP-FPM → Laravel → PostgreSQL
                                      ├─ Google Drive/Docs/Sheets/Forms
                                      └─ SMTP
```

O arquivo `compose.yaml` usa Laravel Sail e existe para desenvolvimento local. Ele expõe portas de desenvolvimento, monta o código do host no container e inclui o Mailpit; não é a configuração de produção deste guia.

Componentes importantes do sistema:

- Laravel 12 com PHP `^8.2`;
- PostgreSQL como banco obrigatório;
- extensões PostgreSQL `unaccent` e `pg_trgm`, criadas pelas migrations;
- assets compilados pelo Vite e servidos a partir de `public/build`;
- sessões e cache usando o banco por padrão;
- integração com Google OAuth e APIs do Drive, Docs, Sheets e Forms;
- backups locais gerados pelo pacote Spatie. A página administrativa gera um backup somente do banco.

## Checklist antes do deploy

Antes de começar, confirme:

- domínio apontando para o IP da VPS;
- acesso SSH e um usuário de deploy separado do `root`;
- firewall permitindo somente SSH, HTTP e HTTPS;
- PostgreSQL criado e protegido, sem necessidade de expor a porta `5432` à Internet;
- credenciais SMTP de produção;
- projeto, credenciais OAuth, IDs das planilhas, formulário, pasta e templates do Google;
- estratégia de backup fora da própria VPS;
- uma janela de manutenção para a primeira publicação.

## 1. Dependências do servidor

Os comandos abaixo são um exemplo para Debian/Ubuntu. Os nomes dos pacotes podem variar conforme a distribuição e a versão do PHP.

```bash
sudo apt update
sudo apt install -y nginx postgresql git unzip curl ca-certificates supervisor \
    php-cli php-fpm php-pgsql php-mbstring php-xml php-curl php-zip
```

Instale também o Composer e uma versão LTS suportada do Node.js/NPM. Depois valide o ambiente:

```bash
php -v
php -m | grep -E 'pdo_pgsql|mbstring|xml|curl|zip'
composer --version
node --version
npm --version
pg_dump --version
```

O `php-zip` é necessário para o recurso de backup que gera arquivos ZIP. O `pg_dump` é usado pelo backup do PostgreSQL.

Ative os serviços necessários e confira o nome do socket do PHP-FPM:

```bash
sudo systemctl enable --now nginx postgresql php8.2-fpm
ls /run/php/php*-fpm.sock
```

Se o servidor usar PHP 8.3 ou outra versão, substitua `php8.2-fpm` e o socket nos exemplos abaixo pela versão instalada.

## 2. Criar banco e usuário do PostgreSQL

Quando o banco estiver na mesma VPS, crie um usuário exclusivo para a aplicação:

```bash
sudo -u postgres psql
```

```sql
CREATE USER sge_iffarsa WITH PASSWORD 'USE_UMA_SENHA_LONGA_E_ALEATORIA';
CREATE DATABASE sge_iffarsa OWNER sge_iffarsa;
\q
```

As migrations criam automaticamente as extensões `unaccent` e `pg_trgm` no PostgreSQL. Se o usuário da aplicação não tiver permissão para criá-las, um administrador do banco deve executá-las uma vez:

```bash
sudo -u postgres psql -d sge_iffarsa -c 'CREATE EXTENSION IF NOT EXISTS unaccent;'
sudo -u postgres psql -d sge_iffarsa -c 'CREATE EXTENSION IF NOT EXISTS pg_trgm;'
```

Não abra a porta `5432` no firewall quando a aplicação e o banco estiverem na mesma máquina. Se o banco for remoto, use o hostname privado e permita a conexão somente a partir do servidor da aplicação.

## 3. Baixar o projeto

Use um usuário de deploy, por exemplo `deploy`, e mantenha o código fora do diretório pessoal do usuário do Nginx. Se ele ainda não existir:

```bash
sudo adduser --disabled-password --gecos "" deploy
sudo usermod -aG www-data deploy
```

Crie o diretório vazio do projeto com o usuário correto:

```bash
sudo install -d -m 755 /var/www
sudo install -d -o deploy -g www-data -m 2775 /var/www/sge-iffarsa
sudo -u deploy git clone https://github.com/estagiosa-cell/SGE-IFFarSA.git /var/www/sge-iffarsa
cd /var/www/sge-iffarsa
```

Se o repositório for privado, configure a autenticação do Git para o usuário `deploy` antes do clone. Não coloque tokens na URL do repositório.

## 4. Configurar o ambiente de produção

Copie o exemplo e edite o arquivo antes de executar os comandos Artisan que dependem da configuração:

```bash
cp .env.example .env
chmod 640 .env
nano .env
```

O `.env.example` contém valores de desenvolvimento, como `APP_DEBUG=true`, `localhost` e Mailpit. Substitua-os. No mínimo, confira estas variáveis:

```env
APP_NAME=SGE-IFFarSA
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://estagios.exemplo.edu.br
APP_TIMEZONE=America/Sao_Paulo
APP_LOCALE=pt_BR
APP_FALLBACK_LOCALE=pt_BR

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=sge_iffarsa
DB_USERNAME=sge_iffarsa
DB_PASSWORD=USE_A_SENHA_DO_BANCO

SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database
FILESYSTEM_DISK=local

MAIL_MAILER=smtp
MAIL_HOST=smtp.exemplo.edu.br
MAIL_PORT=587
MAIL_USERNAME=usuario-smtp
MAIL_PASSWORD=senha-smtp
# Use MAIL_SCHEME conforme o provedor: null/"smtp" para STARTTLS ou "smtps" para SSL.
MAIL_SCHEME=null
MAIL_FROM_ADDRESS="estagios@exemplo.edu.br"
MAIL_FROM_NAME="SGE-IFFarSA"

```

Complete a configuração das APIs e dos IDs do Google na [seção 5](#5-configurar-o-google-cloud-e-os-recursos-da-intranet). O passo a passo também está em [GOOGLE_OAUTH_SETUP.md](GOOGLE_OAUTH_SETUP.md).

Gere a chave somente quando o ambiente ainda não tiver uma. Não execute este comando novamente depois que a aplicação já estiver em uso:

```bash
php artisan key:generate
```

Depois de gerar a chave, preserve o mesmo `APP_KEY` em todos os deploys. Alterá-lo invalida sessões e dados criptografados.

Garanta que o usuário do PHP-FPM consiga ler o arquivo, sem torná-lo público:

```bash
sudo chown deploy:www-data .env
sudo chmod 640 .env
```

## 5. Configurar o Google Cloud e os recursos da intranet

Esta aplicação foi desenhada para uma intranet institucional apoiada pelo Google Workspace. O servidor precisa conseguir acessar as APIs do Google, mas os dados de estágio continuam sendo acessados pelos usuários através da aplicação institucional.

### 5.1 Definir a conta Google do administrador

Crie ou reserve uma conta institucional exclusiva para o SGE-IFFarSA, por exemplo `sge.estagios@instituicao.edu.br`. Essa conta será:

- a conta usada no login/autorização Google do administrador;
- a proprietária ou editora das planilhas, formulário, pasta do Drive e templates usados pelo sistema;
- a identidade em nome da qual o sistema lê e atualiza os dados do Google Workspace.

Não use a conta pessoal de um desenvolvedor. Ative a verificação em duas etapas, configure recuperação institucional e documente quem é responsável por essa conta.

O fluxo de login do administrador tem duas partes: primeiro ele entra no SGE-IFFarSA com o usuário e a senha locais; em seguida, por possuir a role `admin`, o sistema redireciona automaticamente para o OAuth do Google. A conta Google selecionada precisa ser exatamente a informada em `GOOGLE_ADMIN_ACCOUNT_EMAIL`. Depois da autorização, o administrador chega ao dashboard.

O e-mail do primeiro usuário do sistema pode ser o mesmo da conta Google — essa é a opção recomendada —, mas são verificações diferentes: o usuário local autentica no banco e a conta Google é validada pelo `GOOGLE_ADMIN_ACCOUNT_EMAIL`.

### 5.2 Criar o projeto no Google Cloud

Use a conta Google institucional do administrador, ou uma conta institucional responsável pelo projeto, no [Google Cloud Console](https://console.cloud.google.com):

1. Abra **IAM e administrador → Criar um projeto** e crie um projeto exclusivo, por exemplo `SGE-IFFarSA-Produção`.
2. Se a instituição usa Google Workspace, associe o projeto à organização institucional quando essa opção estiver disponível.
3. Em **APIs e serviços → Biblioteca**, ative:
   - Google Drive API;
   - Google Docs API;
   - Google Sheets API;
   - Google Forms API.
4. Em **Google Auth Platform/Tela de consentimento OAuth**, configure o nome da aplicação, o e-mail de suporte e o contato do desenvolvedor.
5. Para um Google Workspace institucional, use o tipo **Interno** quando a política da organização permitir. Caso a aplicação seja configurada como **Externo**, adicione a conta Google administrativa como usuário de teste enquanto o app estiver em teste.
6. O código solicita os escopos de Drive, Sheets, Docs, Forms e e-mail no momento do login. A conta deve aceitar essas permissões. Como são escopos amplos, siga as regras de segurança e verificação da organização/Google antes de publicar o app.
7. Em **Clientes**, crie um cliente OAuth do tipo **Aplicativo da Web**. Adicione a URI autorizada exatamente como será usada pela aplicação:

   ```text
   https://estagios.exemplo.edu.br/google/callback
   ```

   Para desenvolvimento, a URI é `http://localhost:8000/google/callback`. Não adicione uma barra final, troque o protocolo ou use um domínio diferente do `APP_URL` sem cadastrar a nova URI.

8. Copie o **ID do cliente** e o **segredo do cliente** para o `.env` do servidor. O segredo não deve ser commitado nem compartilhado em chamados ou mensagens.

Consulte também a documentação oficial sobre [criação de projetos](https://developers.google.com/workspace/guides/create-project), [ativação das APIs](https://developers.google.com/workspace/guides/enable-apis) e [OAuth para aplicações web](https://developers.google.com/identity/protocols/oauth2/web-server).

### 5.3 Criar e preparar os recursos do Google

Antes do primeiro login funcional, crie ou localize os recursos abaixo usando a conta Google administrativa. Ela precisa ser proprietária ou ter permissão de edição em todos eles:

| Recurso | Variável | Como localizar o ID |
| --- | --- | --- |
| Planilha de coleta de estágios | `GOOGLE_SHEET_ID_DATA_COLLECTION` | trecho entre `/spreadsheets/d/` e `/edit` na URL da planilha |
| Formulário de coleta | `GOOGLE_FORM_ID_DATA_COLLECTION` | trecho entre `/forms/d/` e `/edit` ou `/viewform` na URL |
| Pergunta de orientadores | `GOOGLE_FORM_QUESTION_ID_ADVISORS` | ID interno obtido pelo procedimento abaixo; a pergunta deve ser do tipo lista suspensa |
| Planilha de avaliações de supervisores | `GOOGLE_SHEET_ID_SUPERVISOR_EVALUATION` | trecho entre `/spreadsheets/d/` e `/edit` |
| Pasta de documentos gerados | `GOOGLE_DRIVE_FOLDER_ID` | trecho depois de `/drive/folders/` na URL da pasta |
| Template de termo padrão | `GOOGLE_DOCS_TEMPLATE_ID_TERMO_COMPROMISSO_PADRAO` | trecho entre `/document/d/` e `/edit` na URL do Google Docs |
| Template Emater/RS | `GOOGLE_DOCS_TEMPLATE_ID_TERMO_EMATER_RS` | ID do Google Docs correspondente |
| Template Seduc | `GOOGLE_DOCS_TEMPLATE_ID_TERMO_SEDUC` | ID do Google Docs correspondente |
| Template de rescisão | `GOOGLE_DOCS_TEMPLATE_ID_RESCISAO` | ID do Google Docs correspondente |
| Template de credenciamento | `GOOGLE_DOCS_TEMPLATE_ID_CREDENCIAMENTO` | ID do Google Docs correspondente |
| Template de termo aditivo | `GOOGLE_DOCS_TEMPLATE_TERMO_ADITIVO_TERCEIRA_CLAUSULA` | ID do Google Docs correspondente; esta variável não possui `_ID` no nome por compatibilidade com o código atual |

Na planilha, mantenha as abas, cabeçalhos e ordem de colunas esperados pelo processo de sincronização. Na pergunta de orientadores do Forms, use uma lista suspensa; o sistema atualiza as opções dessa pergunta com orientadores e coordenadores cadastrados.

Para descobrir o ID interno da pergunta de orientadores em produção:

1. Configure primeiro `GOOGLE_FORM_ID_DATA_COLLECTION` e as credenciais OAuth.
2. No servidor, execute `php artisan google:form-ids` como o usuário da aplicação. O comando mantém uma rota temporária ativa.
3. Faça o login local com o primeiro usuário `admin` e autorize a conta Google administrativa.
4. Acesse a URL exibida pelo comando, normalmente `https://estagios.exemplo.edu.br/idform`.
5. No JSON, encontre a pergunta de lista suspensa que contém os orientadores e copie o campo `id` do item — não o título da pergunta nem o ID visível na URL do Forms.
6. Salve o valor em `GOOGLE_FORM_QUESTION_ID_ADVISORS` e encerre o comando com `Ctrl+C`. Isso remove a flag temporária do cache e desativa a rota.

Não deixe `/idform` ativo permanentemente. Mesmo protegida por login administrativo, essa rota existe apenas para configuração e diagnóstico.

### 5.4 Variáveis Google no `.env`

O bloco completo esperado pela configuração atual é:

```env
# OAuth e conta Google administrativa
GOOGLE_CLIENT_ID="ID_DO_CLIENTE_OAUTH"
GOOGLE_CLIENT_SECRET="SEGREDO_DO_CLIENTE_OAUTH"
GOOGLE_REDIRECT_URI="https://estagios.exemplo.edu.br/google/callback"
GOOGLE_ADMIN_ACCOUNT_EMAIL="sge.estagios@instituicao.edu.br"

# Planilhas e formulário de coleta
GOOGLE_SHEET_ID_DATA_COLLECTION="ID_DA_PLANILHA_DE_COLETA"
GOOGLE_FORM_ID_DATA_COLLECTION="ID_DO_FORMULARIO_DE_COLETA"
GOOGLE_FORM_QUESTION_ID_ADVISORS="ID_INTERNO_DA_PERGUNTA_DE_ORIENTADORES"

# Planilha de avaliações de supervisores
GOOGLE_SHEET_ID_SUPERVISOR_EVALUATION="ID_DA_PLANILHA_DE_AVALIACOES"

# Pasta do Drive para os documentos gerados
GOOGLE_DRIVE_FOLDER_ID="ID_DA_PASTA_DO_DRIVE"

# Templates do Google Docs
GOOGLE_DOCS_TEMPLATE_ID_TERMO_COMPROMISSO_PADRAO="ID_DO_TEMPLATE_PADRAO"
GOOGLE_DOCS_TEMPLATE_ID_TERMO_EMATER_RS="ID_DO_TEMPLATE_EMATER_RS"
GOOGLE_DOCS_TEMPLATE_ID_TERMO_SEDUC="ID_DO_TEMPLATE_SEDUC"
GOOGLE_DOCS_TEMPLATE_ID_RESCISAO="ID_DO_TEMPLATE_RESCISAO"
GOOGLE_DOCS_TEMPLATE_ID_CREDENCIAMENTO="ID_DO_TEMPLATE_CREDENCIAMENTO"
GOOGLE_DOCS_TEMPLATE_TERMO_ADITIVO_TERCEIRA_CLAUSULA="ID_DO_TEMPLATE_DE_TERMO_ADITIVO"
```

Depois de alterar variáveis com o cache de configuração já criado, execute:

```bash
php artisan optimize:clear
php artisan optimize
```

## 6. Instalar dependências e preparar a aplicação

Na raiz do projeto:

```bash
composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader
npm ci
npm run build
```

O `npm ci` precisa instalar as dependências de desenvolvimento porque o Vite está em `devDependencies`; elas são usadas somente para gerar os assets. O diretório `node_modules` não precisa permanecer no servidor depois do build, mas será reinstalado pelo script de atualização quando necessário.

Crie os diretórios graváveis e execute as migrations:

```bash
mkdir -p storage/framework/cache/data storage/framework/sessions \
    storage/framework/views storage/app/public bootstrap/cache

php artisan migrate --force
php artisan storage:link
php artisan optimize
```

Não execute `php artisan db:seed --force` em produção. O `DatabaseSeeder` atual cria contas de teste (`admin@teste.com`, `coordenador@teste.com` e `orientador@teste.com`) com senha conhecida.

Para criar o primeiro administrador, use uma credencial temporária criada de forma interativa:

```bash
php artisan tinker
```

```php
\App\Models\User::create([
    'name' => 'Administrador',
    'email' => 'admin@exemplo.edu.br',
    'role' => \App\Enums\UserRole::ADMIN,
    'password' => \Illuminate\Support\Facades\Hash::make('TROQUE-ESTA-SENHA-IMEDIATAMENTE'),
]);
```

Entre na aplicação, troque a senha temporária e cadastre os demais usuários pelo painel administrativo.

Esse é o primeiro usuário local do sistema e deve ter a role `admin`. Ao entrar pela tela `/`, o login ocorre com o e-mail e a senha armazenados no PostgreSQL; em seguida, o sistema redireciona automaticamente esse administrador para o Google OAuth. Selecione a conta Google institucional definida em `GOOGLE_ADMIN_ACCOUNT_EMAIL`, aceite as permissões e confirme que o dashboard abre sem erro. Usuários com as roles `coordenador`, `orientador` e `direcao_ensino` usam o login local e não substituem a conta Google administrativa da integração.

## 7. Permissões

O usuário do deploy deve ser proprietário do código para conseguir atualizar o repositório. O grupo do PHP-FPM precisa escrever somente em `storage` e `bootstrap/cache`:

```bash
sudo chown -R deploy:www-data /var/www/sge-iffarsa
sudo find /var/www/sge-iffarsa -type d -exec chmod 755 {} \;
sudo find /var/www/sge-iffarsa -type f -exec chmod 644 {} \;
sudo find /var/www/sge-iffarsa/storage /var/www/sge-iffarsa/bootstrap/cache \
    -type d -exec chmod 775 {} \;
sudo find /var/www/sge-iffarsa/storage /var/www/sge-iffarsa/bootstrap/cache \
    -type f -exec chmod 664 {} \;
sudo chmod 640 /var/www/sge-iffarsa/.env
```

Não use `chmod -R 777`. Se arquivos criados pelo PHP perderem o grupo correto, revise o usuário/grupo do pool do PHP-FPM e as permissões herdadas dos diretórios.

## 8. Configurar o Nginx

Crie `/etc/nginx/sites-available/sge-iffarsa.conf` apontando o `root` para `public`, nunca para a raiz do repositório:

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name estagios.exemplo.edu.br;

    root /var/www/sge-iffarsa/public;
    index index.php;
    charset utf-8;
    client_max_body_size 25m;

    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt { access_log off; log_not_found off; }

    location ~ \.php$ {
        try_files $uri =404;
        fastcgi_pass unix:/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

Ative o site e valide a configuração:

```bash
sudo ln -s /etc/nginx/sites-available/sge-iffarsa.conf /etc/nginx/sites-enabled/sge-iffarsa.conf
sudo nginx -t
sudo systemctl reload nginx
```

O limite de `25m` cobre os uploads de CSV do sistema, que chegam a 20 MB no backend. Se esse limite for alterado no Nginx, revise também as regras de validação das requisições.

## 9. HTTPS

Depois que o DNS estiver apontando para a VPS, instale um certificado TLS. Em Debian/Ubuntu com Certbot:

```bash
sudo apt install -y certbot python3-certbot-nginx
sudo certbot --nginx -d estagios.exemplo.edu.br
sudo certbot renew --dry-run
```

Após ativar HTTPS, atualize `APP_URL` e `GOOGLE_REDIRECT_URI` no `.env`, confira a URI de redirecionamento no Google Cloud Console e recarregue o cache de configuração:

```bash
php artisan optimize:clear
php artisan optimize
```

## 10. Agendamento e filas

Atualmente não há tarefas registradas no scheduler da aplicação. Se tarefas forem adicionadas futuramente, registre o cron do Laravel no usuário de deploy:

```cron
* * * * * cd /var/www/sge-iffarsa && php artisan schedule:run >> /dev/null 2>&1
```

O projeto está configurado com `QUEUE_CONNECTION=database`. As rotinas atuais enviam e-mails de forma síncrona, então não é necessário um worker dedicado para a instalação atual. Se algum job for alterado para assíncrono, configure um worker persistente; não deixe `queue:work` rodando apenas em uma sessão SSH:

```ini
; /etc/supervisor/conf.d/sge-iffarsa-worker.conf
[program:sge-iffarsa-worker]
process_name=%(program_name)s_%(process_num)02d
command=/usr/bin/php /var/www/sge-iffarsa/artisan queue:work database --sleep=3 --tries=3 --timeout=90
directory=/var/www/sge-iffarsa
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=deploy
numprocs=1
redirect_stderr=true
stdout_logfile=/var/www/sge-iffarsa/storage/logs/worker.log
stopwaitsecs=3600
```

Depois de criar a configuração:

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl status
```

## 11. Backups e restauração

Antes de confiar no backup, valide as dependências e a configuração:

```bash
php -m | grep -E 'pdo_pgsql|zip'
pg_dump --version
php artisan backup:list
```

Backup manual do banco:

```bash
php artisan backup:run --only-db
php artisan backup:list
```

O backup administrativo disponível em `/backup` também usa `--only-db` e inicia o download de um ZIP. A configuração atual grava os arquivos no disco local da VPS; isso não protege contra perda do servidor. Copie os backups para armazenamento externo (S3, outro servidor ou storage institucional) e teste a restauração periodicamente.

O backup completo do pacote inclui a raiz da aplicação. Como isso pode incluir o `.env`, trate o arquivo como material sensível: defina `BACKUP_ARCHIVE_PASSWORD`, mantenha o arquivo fora de áreas públicas e restrinja o acesso ao storage. Configure também o destinatário de notificações em `config/backup.php`; o valor padrão é apenas um placeholder.

Para automatizar banco e limpeza, depois de escolher o horário e o destino externo:

```cron
30 2 * * * cd /var/www/sge-iffarsa && /usr/bin/flock -n /tmp/sge-iffarsa-backup.lock php artisan backup:run --only-db >> storage/logs/backup.log 2>&1
45 2 * * * cd /var/www/sge-iffarsa && /usr/bin/flock -n /tmp/sge-iffarsa-backup.lock php artisan backup:clean >> storage/logs/backup.log 2>&1
```

Faça um teste de restauração em ambiente de homologação. Para um dump PostgreSQL em SQL:

```bash
unzip -l backup-sge-AAAA-MM-DD_HH-MM-SS.zip
unzip backup-sge-AAAA-MM-DD_HH-MM-SS.zip -d /tmp/sge-iffarsa-restore
psql -h 127.0.0.1 -U sge_iffarsa -d sge_iffarsa \
    < /tmp/sge-iffarsa-restore/SGE-IFFarSA/database.sql
```

Confirme o caminho real mostrado por `unzip -l` antes de executar o `psql`. Nunca restaure diretamente no banco de produção sem validar o arquivo e sem ter um backup atual.

## 12. Atualizações com `update-system.sh`

O script deve ser executado na raiz do projeto por um usuário que tenha permissão para atualizar o código:

```bash
cd /var/www/sge-iffarsa
git status --short
php artisan backup:run --only-db
APP_URL=https://estagios.exemplo.edu.br /bin/bash update-system.sh
```

O fluxo do script é:

1. verifica dependências, Git e atualizações no branch atual;
2. coloca a aplicação em manutenção;
3. executa `git reset --hard origin/<branch>`;
4. instala Composer/NPM e recompila os assets;
5. executa as migrations;
6. limpa e recria os caches;
7. retira a aplicação da manutenção e verifica `/up`.

Atenção antes de automatizar:

- `git reset --hard` descarta alterações rastreadas e commits locais que não estejam no remoto. O servidor deve ser tratado como ambiente de publicação, sem alterações manuais no código;
- o script não remove o `.env`, pois ele não é rastreado pelo Git;
- o script usa a variável `APP_URL` do ambiente do shell para o health check. Por isso ela é informada explicitamente no exemplo;
- o script não reinicia o PHP-FPM. Se o servidor usar OPcache com validação de timestamp desativada, recarregue o PHP-FPM após a atualização;
- o script não faz rollback automático de migrations. O backup antes da atualização é obrigatório;
- o script pressupõe uma instalação nativa. Em um ambiente Docker, execute os comandos dentro dos containers e adapte o fluxo.

Após uma atualização, se necessário:

```bash
sudo systemctl reload php8.2-fpm
sudo supervisorctl restart sge-iffarsa-worker:*  # somente se houver worker
```

Para usar o cron, inclua um lock para evitar duas atualizações simultâneas:

```cron
0 3 * * * cd /var/www/sge-iffarsa && /usr/bin/flock -n /tmp/sge-iffarsa-update.lock env APP_URL=https://estagios.exemplo.edu.br /bin/bash update-system.sh >> storage/logs/update.log 2>&1
```

O horário do cron usa o fuso configurado no servidor. Execute o script manualmente algumas vezes e monitore `storage/logs/update.log` antes de habilitar a automação.

## 13. Validação pós-deploy

Execute estes checks após a primeira publicação e após atualizações importantes:

```bash
cd /var/www/sge-iffarsa
composer check-platform-reqs --no-dev
php artisan about
php artisan migrate:status
php artisan route:list
curl -fsS https://estagios.exemplo.edu.br/up
```

No navegador, valide pelo menos:

- abertura da tela de login e login do administrador;
- redefinição de senha com recebimento do e-mail;
- login OAuth usando a conta Google permitida;
- sincronização de dados do Google;
- geração de um documento e acesso ao Google Drive;
- importação de um CSV dentro do limite;
- criação de um backup e conferência de seu conteúdo;
- ausência de erros em `storage/logs/laravel.log` e nos logs do Nginx/PHP-FPM.

## 14. Troubleshooting rápido

| Sintoma | Verificações |
| --- | --- |
| `502 Bad Gateway` | Confirme se o PHP-FPM está ativo e se o socket do Nginx corresponde à versão instalada. |
| Erro `500` | Confira `storage/logs/laravel.log`, `APP_DEBUG=false`, permissões e o cache de configuração. |
| CSS/JS não carrega | Execute `npm run build` e confirme que o Nginx aponta para `/public`. |
| Falha na migration | Teste conexão, `pdo_pgsql` e as extensões `unaccent`/`pg_trgm`. |
| OAuth retorna erro | Compare `APP_URL`, `GOOGLE_REDIRECT_URI` e a URI cadastrada no Google caractere por caractere. |
| E-mail não chega | Revise SMTP, `MAIL_FROM_*`, firewall de saída e os logs da aplicação. |
| Job fica parado | Verifique `QUEUE_CONNECTION`, a tabela `jobs` e o status do Supervisor. |
| Backup falha | Confirme `pg_dump`, `php-zip`, espaço em disco e permissões de `storage`. |

## 15. Segurança mínima

- mantenha `APP_DEBUG=false` em produção;
- nunca versione `.env`, credenciais Google, senhas SMTP ou senhas de backup;
- sirva somente o diretório `public` pelo Nginx;
- use HTTPS e mantenha o certificado renovável;
- limite o firewall a portas necessárias;
- mantenha PHP, Nginx, PostgreSQL, Composer, Node e dependências atualizados;
- monitore logs, espaço em disco, backups e falhas de fila;
- teste restaurações, não apenas a criação dos arquivos de backup.
