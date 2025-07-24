# SGE-IFFarSA - Sistema Integrado de Gestão de Estágios

## Tecnologias Utilizadas

- **Backend**: Laravel 12 (PHP 8.2+)
- **Frontend**: Bootstrap 5, SASS, Vite
- **Banco de Dados**: SQL
- **Servidor de E-mail**: SMTP (configurável via .env)

## Configuração do Ambiente de Desenvolvimento

Siga os passos abaixo para configurar o ambiente de desenvolvimento local.

### Pré-requisitos para Desenvolvimento

- PHP >= 8.2
- Composer
- Node.js & NPM
- Um servidor de banco de dados SQL

### Configuração Passo a Passo

1. **Clone o repositório**:

   ```bash
   git clone https://github.com/ArthurWillers/SGE-IFFarSA.git
   cd seu-repositorio
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

5. **Gere a chave da aplicação**:

   ```bash
   php artisan key:generate
   ```

6. **Configure o Banco de Dados para Desenvolvimento**:

   No arquivo `.env`, edite as variáveis DB\_\* com as credenciais do seu banco de dados de desenvolvimento:

   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=sge_iffarsa
   DB_USERNAME=root
   DB_PASSWORD=
   ```

7. **Execute as Migrations**:

   Este comando irá criar todas as tabelas necessárias no banco de dados:

   ```bash
   php artisan migrate
   ```

8. **Configure o Envio de E-mails para Desenvolvimento**:

   Para que a recuperação de senha e as notificações funcionem no ambiente de desenvolvimento, configure um servidor SMTP no seu arquivo `.env`. Para desenvolvimento, Mailtrap.io é altamente recomendado:

   ```env
   MAIL_MAILER=smtp
   MAIL_HOST=sandbox.smtp.mailtrap.io
   MAIL_PORT=2525
   MAIL_USERNAME=seu_usuario_mailtrap
   MAIL_PASSWORD=sua_senha_mailtrap
   MAIL_FROM_ADDRESS="email@exemplo.com"
   MAIL_FROM_NAME="${APP_NAME}"
   ```

## Executando a Aplicação em Desenvolvimento

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

A aplicação de desenvolvimento estará disponível em `http://localhost:8000`.

## Tarefas Automatizadas (Scheduler)

O projeto utiliza o agendador de tarefas do Laravel para rotinas de manutenção.

### Limpeza de Tokens de Redefinição de Senha

- **O que faz**: Diariamente, o sistema executa o comando `php artisan auth:clear-resets` para remover tokens de redefinição de senha expirados da base de dados.
- **Implementação**: A tarefa está agendada no arquivo `bootstrap/app.php`.
- **Configuração em Produção**: Para que o agendador funcione no servidor de produção, é necessário adicionar a seguinte entrada no cron do servidor:

  ```bash
  * * * * * cd /caminho-para-o-projeto && php artisan schedule:run >> /dev/null 2>&1
  ```

  **Explicação do comando cron:**

  - `* * * * *`: Executa a cada minuto (formato: minuto hora dia mês dia-da-semana)
  - `cd /caminho-para-o-projeto`: Navega para o diretório do projeto
  - `php artisan schedule:run`: Executa o agendador do Laravel que verifica se há tarefas para executar
  - `>> /dev/null 2>&1`: Redireciona toda a saída (normal e de erro) para /dev/null, evitando spam no log do cron
