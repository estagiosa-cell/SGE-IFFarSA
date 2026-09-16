# SGE-IFFarSA - Sistema de Gestão de Estágios

> Sistema pensado para uso em uma intranet institucional. A operação depende do Google Workspace da instituição e o PostgreSQL é obrigatório em desenvolvimento e produção.

## Documentação

- **Guia de Desenvolvimento**: Consulte o arquivo [DEV.md](docs/DEV.md) para instruções detalhadas sobre como configurar o ambiente de desenvolvimento.
- **Guia de Deploy**: Consulte o arquivo [DEPLOY.md](docs/DEPLOY.md) para o passo a passo de produção com Nginx, PHP-FPM, PostgreSQL, HTTPS, backups e atualizações.
- **Guia de Customização**: Consulte o arquivo [CUSTOMIZATION.md](docs/CUSTOMIZATION.md) para instruções sobre como personalizar o sistema (adicionar templates, status, etc.).
- **Configuração do Google OAuth 2.0**: Consulte o arquivo [GOOGLE_OAUTH_SETUP.md](docs/GOOGLE_OAUTH_SETUP.md) para configurar a integração com as APIs do Google.

## Tecnologias Utilizadas

- **Backend**: Laravel 12 (PHP 8.2+)
- **Frontend**: Bootstrap 5, SASS, Vite
- **Banco de Dados**: PostgreSQL (desenvolvimento e produção)
- **Servidor de E-mail**: Configurável via SMTP no arquivo `.env`
- **APIs do Google**: Integração necessária com Drive, Docs, Sheets e Forms para geração, organização e sincronização de dados
