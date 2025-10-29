# Documentação de Configuração: Integração Google OAuth 2.0

Este documento detalha o processo de configuração da integração entre a aplicação SGE-IFFarSA e as APIs do Google, utilizando o fluxo de autenticação OAuth 2.0. O objetivo é permitir que a aplicação execute ações em nome de uma única e específica Conta Google de Administrador, com validação para prevenir o uso de contas incorretas.

## Visão Geral do Fluxo

1. O usuário com perfil de Administrador faz login na aplicação Laravel com e-mail e senha.
2. O Administrador é redirecionado para a tela de login e consentimento do Google.
3. Após a autorização, a aplicação verifica se o e-mail da conta Google autorizada é o e-mail correto do administrador.
4. Se o e-mail for válido, a aplicação recebe as permissões necessárias para interagir com as APIs do Google (Drive, Docs, Sheets) em nome dessa conta.

## Pré-requisitos

- **Conta Google de Administrador**: Uma Conta Google específica que será a "proprietária" dos arquivos e processos. Todas as ações da aplicação serão feitas em nome desta conta. Certifique-se de que esta conta tenha permissões de leitura e escrita nos arquivos do Google Sheets, Google Docs e Google Drive que serão utilizados pela aplicação.
- **Acesso ao Google Cloud Console**: A conta acima precisa ter acesso para criar e gerenciar projetos no [Google Cloud Console](https://console.cloud.google.com).
- **Verificação em Duas Etapas (2SV)**: É obrigatório que a 2SV esteja ativada na Conta Google de Administrador para garantir o acesso contínuo ao Google Cloud.

## Parte 1: Configuração no Google Cloud Console

Siga estes passos para criar as credenciais que a aplicação Laravel usará para se comunicar com o Google.

### 1. Criação do Projeto e Ativação das APIs

1. Acesse o [Google Cloud Console](https://console.cloud.google.com) e crie um novo projeto (ex: `SGE-IFFarSA-Producao`).
2. No menu de navegação (☰), vá para **APIs e serviços > Biblioteca**.
3. Busque e **ATIVE** as seguintes APIs:
   - Google Drive API
   - Google Sheets API
   - Google Docs API

### 2. Configuração da Tela de Consentimento OAuth

1. Vá para **APIs e serviços > Tela de consentimento OAuth**.
2. **Tipo de Usuário**: Selecione `Externo`.
3. **Informações do App**: Preencha o nome do app (ex: `Sistema de Gestão de Estágios - IFFar`) e os e-mails de suporte e contato.
4. **Escopos**: Nesta tela, você não precisa adicionar os escopos manualmente. A aplicação Laravel os solicitará dinamicamente. Apenas clique em `SALVAR E CONTINUAR`.
5. **Usuários de Teste**: Adicione o endereço de e-mail da única Conta Google de Administrador que deve ter permissão para se conectar. Enquanto o app estiver "Em teste", somente esta conta poderá concluir o login.

### 3. Criação das Credenciais (ID do Cliente)

1. Vá para **APIs e serviços > Credenciais**.
2. Clique em `+ CRIAR CREDENCIAIS` e selecione `ID do cliente OAuth`.
3. **Tipo de Aplicativo**: Selecione `Aplicativo da Web`.
4. **URIs de redirecionamento autorizados**: Adicione as URLs para as quais o Google enviará o usuário após a autenticação.
   - Para Desenvolvimento: `http://localhost:8000/google/callback`
   - Para Produção: `https://[URL_DA_SUA_APLICACAO]/google/callback`
5. Clique em `CRIAR`. Copie o **ID do cliente** e a **Chave secreta do cliente**.

## Parte 2: Configuração na Aplicação Laravel (Produção)

A configuração para o ambiente de produção é feita exclusivamente através das variáveis de ambiente.

1. **Acesse as Variáveis de Ambiente**: No seu servidor de produção, edite o arquivo `.env`.
2. **Insira as Credenciais e o E-mail do Admin**: Preencha as seguintes variáveis:

   ```env
   # Credenciais obtidas do Google Cloud Console
   GOOGLE_CLIENT_ID="COLE_O_ID_DO_CLIENTE_AQUI"
   GOOGLE_CLIENT_SECRET="COLE_A_CHAVE_SECRETA_AQUI"
   GOOGLE_REDIRECT_URI="https://[URL_DA_SUA_APLICACAO]/google/callback"

   # E-mail exato da Conta Google que tem permissão para ser usada
   GOOGLE_ADMIN_ACCOUNT_EMAIL="admin.exemplo@dominio.com"
   ```

   > **Atenção**: A variável `GOOGLE_ADMIN_ACCOUNT_EMAIL` é a trava de segurança principal. A aplicação irá rejeitar qualquer tentativa de login com uma conta Google que não corresponda a este e-mail.

## Parte 3: Publicando a Aplicação (Passo Final para Produção)

1. Volte para a **Tela de Consentimento OAuth** no Google Cloud Console.
2. Na seção "Status da publicação", clique no botão `PUBLICAR O APP`.
3. Siga os passos de confirmação.