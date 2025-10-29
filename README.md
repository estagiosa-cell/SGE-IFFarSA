# SGE-IFFarSA - Sistema de Gestão de Estágios

## Documentação

- **Guia de Desenvolvimento**: Consulte o arquivo [DEV.md](docs/DEV.md) para instruções detalhadas sobre como configurar o ambiente de desenvolvimento.
- **Guia de Deploy**: Consulte o arquivo [DEPLOY.md](docs/DEPLOY.md) para instruções detalhadas sobre como realizar o deploy da aplicação.
- **Configuração do Google OAuth 2.0**: Consulte o arquivo [GOOGLE_OAUTH_SETUP.md](docs/GOOGLE_OAUTH_SETUP.md) para configurar a integração com as APIs do Google.

## Tecnologias Utilizadas

- **Backend**: Laravel 12 (PHP 8.2+)
- **Frontend**: Bootstrap 5, SASS, Vite
- **Banco de Dados**: SQLite (desenvolvimento), MySQL/PostgreSQL (produção)
- **Servidor de E-mail**: Configurável via SMTP no arquivo `.env`
- **APIs do Google**: Integração com Drive, Docs e Sheets para geração e organização de documentos

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
