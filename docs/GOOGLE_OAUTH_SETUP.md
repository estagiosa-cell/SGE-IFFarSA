# Configuração do Google Cloud e do Google OAuth — SGE-IFFarSA

O SGE-IFFarSA foi pensado para uso em uma intranet institucional. A aplicação usa o Google Workspace como parte da operação: lê planilhas de coleta, lê avaliações, atualiza uma pergunta do Google Forms e cria documentos no Google Drive a partir de templates do Google Docs.

Por isso, a configuração do Google não é opcional em uma instalação funcional. É necessário criar um projeto no Google Cloud, habilitar as APIs, configurar um cliente OAuth e informar no `.env` os IDs dos recursos usados pela instituição.

## Como a integração funciona

O sistema não usa uma conta de serviço nem um arquivo `credentials.json`. Ele usa OAuth 2.0 em nome de uma única conta Google institucional:

1. o administrador entra no SGE-IFFarSA com o primeiro usuário local, usando e-mail e senha do banco;
2. como esse usuário possui a role `admin`, o sistema o redireciona automaticamente para a autorização do Google;
3. o administrador seleciona a conta Google institucional definida em `GOOGLE_ADMIN_ACCOUNT_EMAIL`;
4. o Google devolve um token com permissões para Drive, Docs, Sheets, Forms e leitura do e-mail;
5. o sistema compara o e-mail retornado pelo Google com `GOOGLE_ADMIN_ACCOUNT_EMAIL`;
6. se a conta estiver correta, o administrador acessa o dashboard e pode sincronizar dados e gerar documentos.

A conta Google escolhida não é uma conta de login para todos os usuários. Ela é a conta Google administrativa usada pela integração. Os demais usuários continuam usando as contas locais cadastradas no SGE-IFFarSA.

O token é mantido na sessão do administrador. Ao iniciar uma nova sessão, pode ser necessário autorizar a conta Google novamente. O fluxo usa acesso offline e renovação do token enquanto a sessão estiver válida.

## Pré-requisitos

- uma conta Google Workspace institucional exclusiva para o sistema, preferencialmente com recuperação e responsabilidade institucional;
- acesso dessa conta, ou de um responsável institucional, ao Google Cloud Console;
- verificação em duas etapas habilitada na conta administrativa;
- domínio da intranet acessível pelo navegador do administrador;
- saída HTTPS do servidor para `www.googleapis.com` e demais endpoints usados pelas APIs;
- uma URL HTTPS de produção para o callback OAuth. Para desenvolvimento, use a URL local indicada abaixo;
- permissão de proprietária ou editora da conta administrativa nas planilhas, formulário, pasta do Drive e templates.

## 1. Definir a conta Google administrativa

Crie ou reserve uma conta como `sge.estagios@instituicao.edu.br`. Essa deve ser a conta que o administrador selecionará na tela do Google durante o login do sistema.

Use essa mesma conta para criar os recursos do Drive/Docs/Sheets/Forms ou compartilhe cada recurso com ela como **Editora**. A integração precisa escrever na planilha, atualizar as opções da pergunta do Forms e criar cópias dos templates na pasta do Drive.

Não use a conta pessoal de um desenvolvedor. Se a instituição substituir essa conta no futuro, será necessário atualizar `GOOGLE_ADMIN_ACCOUNT_EMAIL`, compartilhar novamente os recursos e autorizar a nova conta.

## 2. Criar o projeto no Google Cloud

No [Google Cloud Console](https://console.cloud.google.com), usando a conta institucional ou o responsável pelo Google Workspace:

1. Abra **IAM e administrador → Criar um projeto**.
2. Crie um projeto exclusivo, como `SGE-IFFarSA-Produção`, e associe-o à organização institucional quando essa opção existir.
3. Anote o projeto selecionado antes de continuar; as APIs e credenciais precisam ser criadas nele.

Referência oficial: [criar um projeto do Google Cloud](https://developers.google.com/workspace/guides/create-project).

## 3. Ativar as APIs

No projeto criado, acesse **APIs e serviços → Biblioteca** e habilite:

- **Google Drive API** — acesso à pasta e criação de cópias dos documentos;
- **Google Docs API** — substituição dos placeholders nos documentos;
- **Google Sheets API** — leitura e atualização das planilhas;
- **Google Forms API** — leitura do formulário e atualização da lista de orientadores.

Referência oficial: [habilitar APIs do Google Workspace](https://developers.google.com/workspace/guides/enable-apis).

## 4. Configurar a tela de consentimento OAuth

Abra **Google Auth Platform** ou **APIs e serviços → Tela de consentimento OAuth**, conforme a versão do console:

1. informe o nome da aplicação, por exemplo `Sistema de Gestão de Estágios - IFFar`;
2. informe e-mails institucionais de suporte e contato do desenvolvedor;
3. se a conta pertence a um Google Workspace e a política institucional permitir, prefira o tipo de usuário **Interno**;
4. se o projeto for **Externo**, adicione a conta Google administrativa como usuário de teste enquanto o app estiver em teste;
5. mantenha documentadas as permissões solicitadas e siga as políticas de publicação/verificação do Google e da instituição.

O código atual solicita estes escopos ao iniciar o OAuth:

```text
https://www.googleapis.com/auth/drive
https://www.googleapis.com/auth/spreadsheets
https://www.googleapis.com/auth/documents
https://www.googleapis.com/auth/forms.body
https://www.googleapis.com/auth/userinfo.email
```

Esses escopos dão acesso amplo aos recursos correspondentes da conta administrativa. Conceda autorização somente à conta institucional designada e não compartilhe o segredo do cliente.

## 5. Criar o cliente OAuth para aplicação web

Em **Google Auth Platform → Clientes** ou **APIs e serviços → Credenciais**:

1. selecione **Criar cliente** ou **Criar credenciais → ID do cliente OAuth**;
2. escolha **Aplicativo da Web**;
3. registre a URI de redirecionamento de produção exatamente como será usada no `.env`:

   ```text
   https://estagios.exemplo.edu.br/google/callback
   ```

4. para desenvolvimento, registre também:

   ```text
   http://localhost:8000/google/callback
   ```

5. copie o **ID do cliente** e o **segredo do cliente**.

A URI é comparada exatamente pelo Google: protocolo (`http`/`https`), domínio, caminho, maiúsculas/minúsculas e barra final precisam coincidir. Uma diferença causa `redirect_uri_mismatch`.

Referências oficiais: [credenciais do Google Workspace](https://developers.google.com/workspace/guides/create-credentials) e [OAuth para aplicações web](https://developers.google.com/identity/protocols/oauth2/web-server).

## 6. Criar os recursos e localizar os IDs

Crie ou localize os recursos abaixo usando a conta Google administrativa. Os IDs são diferentes para cada recurso e devem ser copiados sem aspas extras além das usadas no `.env`.

| Recurso | Variável de ambiente | Como obter |
| --- | --- | --- |
| Planilha de coleta de estágios | `GOOGLE_SHEET_ID_DATA_COLLECTION` | valor entre `/spreadsheets/d/` e `/edit` na URL |
| Formulário de coleta | `GOOGLE_FORM_ID_DATA_COLLECTION` | valor entre `/forms/d/` e `/edit` ou `/viewform` na URL |
| Pergunta de orientadores | `GOOGLE_FORM_QUESTION_ID_ADVISORS` | ID interno do item do Forms, obtido pelo procedimento da seção 7 |
| Planilha de avaliações dos supervisores | `GOOGLE_SHEET_ID_SUPERVISOR_EVALUATION` | valor entre `/spreadsheets/d/` e `/edit` na URL |
| Pasta dos documentos gerados | `GOOGLE_DRIVE_FOLDER_ID` | valor depois de `/drive/folders/` na URL |
| Template de termo de compromisso padrão | `GOOGLE_DOCS_TEMPLATE_ID_TERMO_COMPROMISSO_PADRAO` | valor entre `/document/d/` e `/edit` na URL |
| Template de termo Emater/RS | `GOOGLE_DOCS_TEMPLATE_ID_TERMO_EMATER_RS` | ID do documento Google Docs correspondente |
| Template de termo Seduc | `GOOGLE_DOCS_TEMPLATE_ID_TERMO_SEDUC` | ID do documento Google Docs correspondente |
| Template de rescisão | `GOOGLE_DOCS_TEMPLATE_ID_RESCISAO` | ID do documento Google Docs correspondente |
| Template de credenciamento | `GOOGLE_DOCS_TEMPLATE_ID_CREDENCIAMENTO` | ID do documento Google Docs correspondente |
| Template de termo aditivo | `GOOGLE_DOCS_TEMPLATE_TERMO_ADITIVO_TERCEIRA_CLAUSULA` | ID do documento Google Docs correspondente; o nome não possui `_ID` por compatibilidade |

### Estrutura esperada das planilhas

A sincronização atual procura a aba chamada exatamente `Respostas ao formulário 1`:

- na planilha de coleta de estágios, lê o intervalo `B2:BJ` e grava a coluna `BJ` como controle de processamento;
- na planilha de avaliações, lê o intervalo `A2:Z` e grava o controle na coluna esperada pelo processo.

Não renomeie essa aba nem altere a ordem dos cabeçalhos sem revisar `app/Http/Controllers/Admin/SyncDataController.php`. A conta administrativa precisa ter acesso de edição às duas planilhas.

### Templates do Google Docs

Os templates precisam estar acessíveis à conta administrativa. O sistema cria uma cópia do template, coloca essa cópia em `GOOGLE_DRIVE_FOLDER_ID` e substitui os placeholders definidos no controller de documentos.

Se um template não for encontrado, confira o ID, a permissão de compartilhamento e se a conta selecionada no OAuth é realmente a conta configurada em `GOOGLE_ADMIN_ACCOUNT_EMAIL`.

## 7. Descobrir o ID interno da pergunta de orientadores

O ID usado por `GOOGLE_FORM_QUESTION_ID_ADVISORS` é o ID interno do item/pergunta retornado pela API do Forms; ele não é o título da pergunta nem necessariamente um valor visível na URL.

Depois de configurar o projeto, o OAuth e `GOOGLE_FORM_ID_DATA_COLLECTION`:

1. crie o primeiro usuário local do sistema seguindo a seção 9;
2. no servidor, execute:

   ```bash
   php artisan google:form-ids
   ```

3. mantenha o comando rodando; ele libera temporariamente a rota `/idform`;
4. faça login no sistema com o usuário local `admin` e autorize a conta Google administrativa;
5. acesse a URL exibida no terminal, normalmente `https://estagios.exemplo.edu.br/idform`;
6. no JSON, localize a pergunta de tipo **lista suspensa** que contém os orientadores e copie o valor do campo `id`;
7. salve esse valor em `GOOGLE_FORM_QUESTION_ID_ADVISORS`;
8. encerre o comando com `Ctrl+C` para remover a flag do cache e desativar a rota temporária.

A pergunta precisa ser do tipo lista suspensa porque o sistema atualiza suas opções com os orientadores e coordenadores cadastrados.

Em desenvolvimento, o comando equivalente é `./vendor/bin/sail artisan google:form-ids` e a URL normalmente começa com `http://localhost:8000`.

## 8. Configurar o `.env` da aplicação

Além das variáveis gerais do Laravel, preencha todo o bloco Google abaixo no servidor. Não deixe placeholders como `<id_do_seu_formulario>` em produção:

```env
# Credenciais do cliente OAuth Web
GOOGLE_CLIENT_ID="ID_DO_CLIENTE_OAUTH"
GOOGLE_CLIENT_SECRET="SEGREDO_DO_CLIENTE_OAUTH"
GOOGLE_REDIRECT_URI="https://estagios.exemplo.edu.br/google/callback"

# E-mail exato da única conta Google administrativa autorizada
GOOGLE_ADMIN_ACCOUNT_EMAIL="sge.estagios@instituicao.edu.br"

# Planilha e formulário de coleta
GOOGLE_SHEET_ID_DATA_COLLECTION="ID_DA_PLANILHA_DE_COLETA"
GOOGLE_FORM_ID_DATA_COLLECTION="ID_DO_FORMULARIO_DE_COLETA"
GOOGLE_FORM_QUESTION_ID_ADVISORS="ID_INTERNO_DA_PERGUNTA_DE_ORIENTADORES"

# Planilha de avaliações de supervisores
GOOGLE_SHEET_ID_SUPERVISOR_EVALUATION="ID_DA_PLANILHA_DE_AVALIACOES"

# Pasta de destino no Google Drive
GOOGLE_DRIVE_FOLDER_ID="ID_DA_PASTA_DO_DRIVE"

# Templates do Google Docs
GOOGLE_DOCS_TEMPLATE_ID_TERMO_COMPROMISSO_PADRAO="ID_DO_TEMPLATE_PADRAO"
GOOGLE_DOCS_TEMPLATE_ID_TERMO_EMATER_RS="ID_DO_TEMPLATE_EMATER_RS"
GOOGLE_DOCS_TEMPLATE_ID_TERMO_SEDUC="ID_DO_TEMPLATE_SEDUC"
GOOGLE_DOCS_TEMPLATE_ID_RESCISAO="ID_DO_TEMPLATE_RESCISAO"
GOOGLE_DOCS_TEMPLATE_ID_CREDENCIAMENTO="ID_DO_TEMPLATE_CREDENCIAMENTO"
GOOGLE_DOCS_TEMPLATE_TERMO_ADITIVO_TERCEIRA_CLAUSULA="ID_DO_TEMPLATE_DE_TERMO_ADITIVO"
```

O valor de `GOOGLE_ADMIN_ACCOUNT_EMAIL` é comparado diretamente com o e-mail retornado pelo Google. Use o endereço exato da conta, sem alias ou conta pessoal.

Depois de editar o `.env` em uma instalação que já tenha cache de configuração:

```bash
php artisan optimize:clear
php artisan optimize
```

## 9. Criar o primeiro usuário do sistema

O login Google não cria o usuário local automaticamente. Primeiro é preciso criar um registro na tabela `users` com a role `admin`. Depois esse usuário fará o login local e será redirecionado ao OAuth.

Após executar as migrations do deploy:

```bash
php artisan tinker
```

```php
\App\Models\User::create([
    'name' => 'Administrador',
    'email' => 'admin@instituicao.edu.br',
    'role' => \App\Enums\UserRole::ADMIN,
    'password' => \Illuminate\Support\Facades\Hash::make('SENHA_TEMPORARIA_FORTE'),
]);
```

Recomenda-se usar como e-mail local o mesmo endereço da conta Google administrativa, embora o código faça as duas validações separadamente. Entre em `/`, use o e-mail e a senha temporária, selecione a conta Google correta, aceite as permissões e troque a senha depois do primeiro acesso.

Não execute o `DatabaseSeeder` em produção: ele cria usuários de teste com senhas conhecidas. Consulte [DEPLOY.md](DEPLOY.md) para o fluxo completo de instalação e permissões.

## 10. Checklist de validação

Depois de configurar tudo:

- `GOOGLE_CLIENT_ID` e `GOOGLE_CLIENT_SECRET` pertencem ao projeto correto;
- `GOOGLE_REDIRECT_URI` é idêntica à URI cadastrada no cliente OAuth;
- `GOOGLE_ADMIN_ACCOUNT_EMAIL` é o e-mail exato da conta institucional;
- todas as APIs listadas na seção 3 estão habilitadas;
- a conta administrativa pode editar as duas planilhas e o formulário;
- a conta administrativa pode ler os templates e gravar na pasta do Drive;
- o usuário local com role `admin` foi criado;
- o login local redireciona para o Google;
- o Google retorna para `/google/callback` e o dashboard abre;
- a sincronização de dados funciona;
- a geração de um documento cria a cópia na pasta correta;
- a lista de orientadores pode ser atualizada no Forms.

## Problemas comuns

| Erro ou sintoma | Causa provável |
| --- | --- |
| `redirect_uri_mismatch` | A URI do `.env` difere da URI cadastrada no cliente OAuth. Confira protocolo, domínio e barra final. |
| `Conta Google não autorizada` | A conta selecionada não é exatamente `GOOGLE_ADMIN_ACCOUNT_EMAIL`. |
| Erro ao acessar planilha | ID incorreto, planilha sem compartilhamento ou API Sheets desabilitada. |
| Erro ao acessar formulário | ID interno da pergunta incorreto, formulário sem permissão ou API Forms desabilitada. |
| Template não encontrado | ID do Docs incorreto ou template não compartilhado com a conta administrativa. |
| Documento salvo no lugar errado | `GOOGLE_DRIVE_FOLDER_ID` ausente/incorreto ou conta sem permissão na pasta. |
| `/idform` retorna 404 | O comando `google:form-ids` não está rodando ou foi encerrado. |
| OAuth não abre na intranet | O navegador não alcança o domínio da aplicação ou o servidor não tem saída HTTPS para o Google. |

## Segurança

- mantenha o segredo OAuth apenas no `.env` do servidor;
- não versione `.env`, tokens, exportações de credenciais ou arquivos de backup;
- use uma conta institucional exclusiva, com 2SV e responsável definido;
- conceda acesso aos recursos somente à conta administrativa e às pessoas necessárias;
- encerre o comando `google:form-ids` assim que o ID for descoberto;
- revogue o acesso no Google Cloud se a conta administrativa for substituída;
- após alterar variáveis, limpe e recrie o cache de configuração.
