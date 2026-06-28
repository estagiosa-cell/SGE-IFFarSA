# Guia de Customização - SGE-IFFarSA

Este documento descreve como realizar personalizações comuns no sistema, como adicionar novos templates de documentos, modificar status de estágios, ajustar papéis de usuários, entre outros.

## Índice

- [Adicionar Novo Template de Documento](#adicionar-novo-template-de-documento)
- [Modificar Status de Estágios](#modificar-status-de-estágios)
- [Adicionar Novos Papéis de Usuário](#adicionar-novos-papéis-de-usuário)
- [Adicionar Novos Placeholders em Documentos](#adicionar-novos-placeholders-em-documentos)
- [Configurar Novos Tipos de Documentos](#configurar-novos-tipos-de-documentos)

---

## Adicionar Novo Template de Documento

Para adicionar um novo template de documento no Google Docs:

### 1. Criar o Template no Google Drive

1. Acesse o Google Docs e crie um novo documento
2. Adicione os placeholders necessários no formato `{{NOME_PLACEHOLDER}}`
3. Formate o documento conforme desejado
4. Copie o ID do documento da URL (a parte após `/document/d/`)

### 2. Adicionar a Variável de Ambiente

Edite o arquivo `.env` e adicione a nova variável:

```env
GOOGLE_DOCS_TEMPLATE_ID_NOME_DO_TEMPLATE="seu_id_aqui"
```

**Exemplo:**
```env
GOOGLE_DOCS_TEMPLATE_ID_RELATORIO_FINAL="1AbCdEfGhIjKlMnOpQrStUvWxYz"
```

### 3. Adicionar no `.env.example`

Não esqueça de adicionar também no arquivo `.env.example` para documentação:

```env
# Template para o relatório final do estágio
GOOGLE_DOCS_TEMPLATE_ID_RELATORIO_FINAL="<id_do_template_relatorio>"
```

### 4. Atualizar o Controller

Edite o arquivo `app/Http/Controllers/Admin/InternshipDocumentController.php` e adicione o novo tipo de documento no método `__invoke()`:

```php
$documentConfig = match ($documentType) {
    'padrao' => [
        'template_id' => config('services.google.docs_template_id_termo_compromisso_padrao'),
        'title' => "Termo de Compromisso - {$internship->student_name} - {$currentDateTime}",
    ],
    // ... outros casos existentes ...
    'relatorio_final' => [
        'template_id' => config('services.google.docs_template_id_relatorio_final'),
        'title' => "Relatório Final - {$internship->student_name} - {$currentDateTime}",
    ],
};
```

### 5. Registrar no Config

Edite o arquivo `config/services.php` e adicione a configuração:

```php
'google' => [
    // ... configurações existentes ...
    'docs_template_id_relatorio_final' => env('GOOGLE_DOCS_TEMPLATE_ID_RELATORIO_FINAL'),
],
```

### 6. Adicionar na Interface

Se você deseja que o novo template apareça na interface, edite a view correspondente (provavelmente em `resources/views/admin/internships/edit.blade.php` ou similar) e adicione a nova opção:

```html
<option value="relatorio_final">Relatório Final</option>
```

---

## Modificar Status de Estágios

Os status de estágios são definidos no Enum `app/Enums/InternshipStatus.php`.

### Adicionar Novo Status

1. Edite o arquivo `app/Enums/InternshipStatus.php`
2. Adicione o novo caso (case) no Enum:

```php
enum InternshipStatus: string
{
    case PENDING = 'Pendente';
    case AWAITING_SIGNATURE = 'Aguardando Assinatura';
    case IN_PROGRESS = 'Em Andamento';
    case COMPLETED = 'Concluído';
    case CANCELLED = 'Cancelado';
    case SEU_NOVO_STATUS = 'Seu Novo Status'; // Adicione aqui
}
```

3. Atualize o método `label()`:

```php
public function label(): string
{
    return match ($this) {
        self::PENDING => 'Pendente',
        self::AWAITING_SIGNATURE => 'Aguardando Assinatura',
        self::RELEASED => 'Liberado',
        self::IN_PROGRESS => 'Em Andamento',
        self::COMPLETED => 'Concluído',
        self::CANCELLED => 'Cancelado',
        self::SEU_NOVO_STATUS => 'Seu Novo Status', // Adicione aqui
    };
}
```

4. Atualize o método `color()` para definir a cor do badge:

```php
public function color(): string
{
    return match ($this) {
        self::PENDING => 'warning',
        self::AWAITING_SIGNATURE => 'info',
        self::RELEASED => 'secondary',
        self::IN_PROGRESS => 'primary',
        self::COMPLETED => 'success',
        self::CANCELLED => 'danger',
        self::SEU_NOVO_STATUS => 'secondary', // Adicione aqui (warning, info, primary, success, danger, secondary)
    };
}
```

### Ajustar Ordem de Exibição

A ordem de exibição na listagem agora é automaticamente determinada pela ordem de declaração dos casos (cases) dentro do Enum `App\Enums\InternshipStatus`.

Se você deseja que o novo status apareça em uma ordem específica (ex: antes de *Concluído*), basta mover a linha com o caso `SEU_NOVO_STATUS` para a posição desejada dentro do arquivo `app/Enums/InternshipStatus.php`:

```php
enum InternshipStatus: string
{
    case PENDING = 'Pendente';
    case AWAITING_SIGNATURE = 'Aguardando Assinatura';
    case RELEASED = 'Liberado';
    case IN_PROGRESS = 'Em Andamento';
    case 
    case SEU_NOVO_STATUS = 'Seu Novo Status'; // Declarado antes do Concluído
    case COMPLETED = 'Concluído';
}
```

O método `InternshipStatus::orderSql()` fará com que as queries nas *Controllers* (como `InternshipController`, `InternshipViewController` e `TeachingDirectorController`) gerem os valores corretos no `CASE SQL` dinamicamente com base nessa ordem definida no arquivo.

### Remover ou Renomear Status

⚠️ **ATENÇÃO:** Antes de remover ou renomear um status, certifique-se de que não há registros usando esse status no banco de dados, ou crie uma migration para atualizar os registros existentes.

**Exemplo de migration para atualizar status existentes:**

```bash
./vendor/bin/sail artisan make:migration update_old_status_to_new_status
```

```php
public function up()
{
    DB::table('internships')
        ->where('status', 'Status Antigo')
        ->update(['status' => 'Status Novo']);
}
```

---

## Adicionar Novos Papéis de Usuário

Os papéis (roles) de usuários são definidos no Enum `app/Enums/UserRole.php`.

### Adicionar Nova Role

1. Edite o arquivo `app/Enums/UserRole.php`
2. Adicione o novo caso:

```php
enum UserRole: string
{
    case ADMIN = 'admin';
    case COORDENADOR = 'coordenador';
    case ORIENTADOR = 'orientador';
    case NOVA_ROLE = 'nova_role'; // Adicione aqui
}
```

3. Atualize o método `label()`:

```php
public function label(): string
{
    return match($this) {
        self::ADMIN => 'Administrador',
        self::COORDENADOR => 'Coordenador',
        self::ORIENTADOR => 'Orientador',
        self::NOVA_ROLE => 'Nome da Nova Role', // Adicione aqui
    };
}
```

### Configurar Permissões para a Nova Role

Após adicionar a nova role, você precisa configurar as permissões nas Policies. Edite os arquivos em `app/Policies/`:

**Exemplo em `InternshipPolicy.php`:**

```php
public function viewAny(User $user): bool
{
    return in_array($user->role, [
        UserRole::ADMIN->value,
        UserRole::COORDENADOR->value,
        UserRole::ORIENTADOR->value,
        UserRole::NOVA_ROLE->value, // Adicione aqui se tiver permissão
    ]);
}
```

### Atualizar Login Redirect

Se a nova role precisa de um redirecionamento específico após o login, edite `app/Services/LoginRedirectService.php`:

```php
public function getRedirectRoute(User $user): string
{
    return match ($user->role) {
        UserRole::ADMIN->value => 'admin.dashboard',
        UserRole::COORDENADOR->value => 'admin.dashboard',
        UserRole::ORIENTADOR->value => 'admin.internships.index',
        UserRole::NOVA_ROLE->value => 'rota.especifica', // Adicione aqui
        default => 'admin.dashboard',
    };
}
```

---

## Adicionar Novos Placeholders em Documentos

Para adicionar novos campos que serão substituídos nos documentos gerados:

### 1. Adicionar Coluna no Banco de Dados (se necessário)

Se o novo campo precisa ser persistido, crie uma migration:

```bash
./vendor/bin/sail artisan make:migration add_novo_campo_to_internships_table
```

```php
public function up()
{
    Schema::table('internships', function (Blueprint $table) {
        $table->string('novo_campo')->nullable()->after('campo_existente');
    });
}

public function down()
{
    Schema::table('internships', function (Blueprint $table) {
        $table->dropColumn('novo_campo');
    });
}
```

Execute a migration:
```bash
./vendor/bin/sail artisan migrate
```

### 2. Adicionar ao Model (se necessário)

Edite `app/Models/Internship.php` e adicione o campo ao array `$fillable`:

```php
protected $fillable = [
    // ... campos existentes ...
    'novo_campo',
];
```

### 3. Adicionar ao Método `getReplacements()`

Edite `app/Http/Controllers/Admin/InternshipDocumentController.php`, método `getReplacements()`:

```php
return [
    // ... placeholders existentes ...
    '{{NOVO_PLACEHOLDER}}' => $internship->novo_campo ?? '',
];
```

### 4. Adicionar no Template do Google Docs

Edite o template no Google Docs e adicione o placeholder `{{NOVO_PLACEHOLDER}}` onde desejar.

### 5. Atualizar Formulário (se necessário)

Se o campo deve ser editável, adicione-o ao formulário de edição em `resources/views/admin/internships/edit.blade.php`:

```html
<div class="mb-3">
    <label for="novo_campo" class="form-label">Novo Campo</label>
    <input type="text" class="form-control" id="novo_campo" 
           name="novo_campo" value="{{ old('novo_campo', $internship->novo_campo) }}">
</div>
```

E adicione a validação em `app/Http/Requests/UpdateInternshipRequest.php`:

```php
public function rules(): array
{
    return [
        // ... regras existentes ...
        'novo_campo' => ['nullable', 'string', 'max:255'],
    ];
}
```

---

## Configurar Novos Tipos de Documentos

Se você precisa adicionar um tipo completamente novo de documento (diferente dos Termos de Compromisso):

### 1. Criar o Template no Google Docs

Siga os passos da seção [Adicionar Novo Template de Documento](#adicionar-novo-template-de-documento).

### 2. Criar Novo Controller (Opcional)

Para documentos muito diferentes, considere criar um controller dedicado:

```bash
./vendor/bin/sail artisan make:controller Admin/NovoDocumentoController --invokable
```

### 3. Adicionar Rota

Edite `routes/web.php`:

```php
Route::post('/admin/internships/{internship}/novo-documento', 
    [NovoDocumentoController::class, '__invoke'])
    ->name('admin.internships.novo-documento');
```

### 4. Adicionar Botão na Interface

Adicione o botão na view apropriada:

```html
<form method="POST" action="{{ route('admin.internships.novo-documento', $internship) }}">
    @csrf
    <button type="submit" class="btn btn-primary">
        Gerar Novo Documento
    </button>
</form>
```

---

## Dicas Gerais

### Limpeza de Cache

Após modificações em Enums ou configurações, sempre limpe o cache:

```bash
./vendor/bin/sail artisan config:clear
./vendor/bin/sail artisan cache:clear
./vendor/bin/sail artisan view:clear
```

Em produção, reconstrua os caches:

```bash
./vendor/bin/sail artisan config:cache
./vendor/bin/sail artisan route:cache
./vendor/bin/sail artisan view:cache
```

### Teste Sempre

Após qualquer modificação:

1. Teste em ambiente de desenvolvimento
2. Verifique os logs em `storage/logs/laravel.log`
3. Teste todas as funcionalidades afetadas

### Backup

Antes de modificações significativas, faça backup do banco de dados:

```bash
./vendor/bin/sail artisan backup:run
```

### Versionamento

Sempre faça commit das mudanças no Git:

```bash
git add .
git commit -m "Adiciona novo template/status/feature"
git push
```

---

## Troubleshooting

### Novo campo não aparece no documento gerado

- Verifique se o placeholder está corretamente escrito no template do Google Docs
- Confirme que o placeholder foi adicionado ao método `getReplacements()`
- Verifique se há dados no campo do banco de dados

### Erro ao gerar documento

- Verifique os logs em `storage/logs/laravel.log`
- Confirme que o ID do template está correto no arquivo `.env`
- Verifique as permissões de compartilhamento do template no Google Drive
- Certifique-se de que a conta de serviço tem acesso ao template

### Novo status não aparece no dropdown

- Limpe o cache: `./vendor/bin/sail artisan config:clear`
- Verifique se o método `options()` do Enum está correto
- Confirme que a view está usando `InternshipStatus::options()`

---

## Precisa de Ajuda?

Para modificações mais complexas ou dúvidas sobre o sistema:

1. Consulte a documentação do Laravel: https://laravel.com/docs
2. Revise os arquivos existentes para entender os padrões do projeto
3. Verifique os comentários no código para entender a lógica
