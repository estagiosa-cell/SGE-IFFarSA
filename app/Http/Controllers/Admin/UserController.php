<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Controlador para gerenciar usuários no painel administrativo.
 *
 * Este controlador lida com a listagem, criação, edição, exclusão,
 * importação e outras operações relacionadas aos usuários do sistema.
 */
class UserController extends Controller
{
    use AuthorizesRequests;

    /**
     * Exibe uma lista de usuários com filtros.
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', User::class);

        // Inicia a query para buscar usuários.
        $query = User::query();

        // Verifica se o filtro 'show_deleted' está ativo para incluir usuários removidos (soft delete).
        $showDeleted = $request->input('show_deleted') === '1';
        if ($showDeleted) {
            $query = $query->onlyTrashed();
        }

        // Filtra os usuários por papel (role), se especificado.
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        // Filtra os usuários por status (ativo/inativo), se especificado.
        if ($request->filled('status')) {
            if ($request->input('status') === 'active') {
                $query->whereNull('deactivated_at');
            } elseif ($request->input('status') === 'inactive') {
                $query->whereNotNull('deactivated_at');
            }
        }

        $orderedQuery = $query->latest();
        $users = $orderedQuery->search($request->input('search'), ['name', 'email'])
            ->paginate(100)
            ->withQueryString();

        // Pagina os resultados e busca todos os papéis para o formulário de filtro.
        $roles = UserRole::cases();

        // Computa contagem de filtros ativos para a view
        $activeFiltersCount = collect([
            $request->input('search'),
            $request->input('role'),
            $request->input('status'),
        ])->filter(fn ($v) => filled($v))->count();

        // Retorna a view com os dados.
        return view('admin.users.index', compact('users', 'roles', 'showDeleted', 'activeFiltersCount'));
    }

    /**
     * Exibe o formulário para criar um novo usuário.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $this->authorize('create', User::class);

        // Busca todos os papéis de usuário para o formulário.
        $roles = UserRole::cases();

        // Retorna a view do formulário de criação.
        return view('admin.users.create', compact('roles'));
    }

    /**
     * Armazena um novo usuário no banco de dados.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(StoreUserRequest $request)
    {
        // Obtém os dados validados da requisição.
        $data = $request->validated();

        // Gera uma senha aleatória forte para o novo usuário.
        $data['password'] = Str::random(40);

        // Cria o novo usuário no banco de dados.
        User::create($data);

        // Redireciona para a lista de usuários com uma mensagem de sucesso.
        return redirect()->route('admin.users.index')
            ->with('message', 'Usuário cadastrado com sucesso!')
            ->with('messageType', 'success');
    }

    /**
     * Exibe o formulário para editar um usuário existente.
     *
     * @param  \App\Models\User  $user  O usuário a ser editado.
     * @return \Illuminate\View\View
     */
    public function edit(User $user)
    {
        $this->authorize('update', $user);

        // Busca todos os papéis de usuário para o formulário.
        $roles = UserRole::cases();

        // Retorna a view de edição com os dados do usuário e os papéis.
        return view('admin.users.edit', compact('user', 'roles'));
    }

    /**
     * Atualiza um usuário específico no banco de dados.
     *
     * @param  \App\Models\User  $user  O usuário a ser atualizado.
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(UpdateUserRequest $request, User $user)
    {
        try {
            // A autorização já foi feita pelo UpdateUserRequest, mas podemos chamar de novo se quisermos.
            // $this->authorize('update', $user);

            // Obtém os dados validados da requisição.
            $data = $request->validated();

            // Atualiza os dados do usuário.
            $user->update($data);

            // Redireciona para a página de edição com uma mensagem de sucesso.
            return redirect()->route('admin.users.edit', $user->id)
                ->with('message', 'Usuário atualizado com sucesso!')
                ->with('messageType', 'success');
        } catch (AuthorizationException $e) {
            return back()
                ->with('message', $e->getMessage())
                ->with('messageType', 'danger');
        }
    }

    /**
     * Importa usuários a partir de um arquivo CSV.
     *
     * O arquivo CSV deve conter nome e e-mail. Os usuários são criados
     * com o papel de Orientador e uma senha aleatória.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function import(Request $request)
    {
        $this->authorize('create', User::class);

        // Valida o arquivo enviado.
        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:10240', // max 10MB
        ]);

        $file = $request->file('file');
        $handle = fopen($file->getRealPath(), 'r');
        fgetcsv($handle); // Pula a linha do cabeçalho do CSV.

        // Lê as linhas do CSV e armazena em um array.
        $rows = [];
        while (($row = fgetcsv($handle)) !== false) {
            // Garante que as colunas essenciais (nome e e-mail) existem.
            if (isset($row[0]) && isset($row[1])) {
                $rows[] = $row;
            }
        }
        fclose($handle);

        // Extrai os e-mails do CSV para verificação.
        $emails = array_map(fn ($r) => $r[1], $rows);
        // Verifica se algum dos e-mails já existe no banco de dados.
        $existingEmails = User::whereIn('email', $emails)->pluck('email')->toArray();

        // Se houver e-mails duplicados, retorna um erro com a lista de e-mails.
        if (! empty($existingEmails)) {
            return back()->with('importStatus', 'Os seguintes e-mails já existem no sistema: <br>'.implode('<br>', $existingEmails));
        }

        // Prepara os dados para inserir de uma vez só (Batch Insert)
        $now = now();
        $usersToInsert = [];
        foreach ($rows as $row) {
            $usersToInsert[] = [
                'name' => $row[0],
                'email' => $row[1],
                'role' => UserRole::ORIENTADOR->value, // Pegando o valor explícito do enum
                'password' => bcrypt(Str::random(40)),
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        if (! empty($usersToInsert)) {
            User::insert($usersToInsert);
        }

        return redirect()->route('admin.users.index')
            ->with('message', 'Usuários importados com sucesso!')
            ->with('messageType', 'success');
    }

    /**
     * Remove um usuário do sistema (soft delete).
     *
     * @param  \App\Models\User  $user  O usuário a ser excluído.
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(User $user)
    {
        try {
            $this->authorize('delete', $user);
            $user->delete();
        } catch (AuthorizationException $e) {
            return back()->with('message', $e->getMessage())->with('messageType', 'danger');
        }

        return redirect()->route('admin.users.index')
            ->with('message', 'Usuário excluído com sucesso!')
            ->with('messageType', 'success');
    }

    /**
     * Restaura um usuário que foi removido via soft delete.
     *
     * @param  string  $id  O ID do usuário a ser restaurado.
     * @return \Illuminate\Http\RedirectResponse
     */
    public function restore($id)
    {
        // Busca o usuário apenas na lixeira (onlyTrashed).
        $user = User::onlyTrashed()->findOrFail($id);
        $this->authorize('restore', $user);
        $user->restore();

        // Redireciona de volta para a lista de usuários excluídos.
        return redirect()->route('admin.users.index', ['show_deleted' => 1])
            ->with('message', 'Usuário restaurado com sucesso!')
            ->with('messageType', 'success');
    }

    /**
     * Desativa a conta de um usuário.
     *
     * @param  \App\Models\User  $user  O usuário a ser desativado.
     * @return \Illuminate\Http\RedirectResponse
     */
    public function deactivate(User $user)
    {
        try {
            $this->authorize('deactivate', $user);
            // Define a data de desativação para o momento atual.
            $user->update(['deactivated_at' => now()]);
        } catch (AuthorizationException $e) {
            return back()->with('message', $e->getMessage())->with('messageType', 'danger');
        }

        return back()->with('message', 'Usuário desativado com sucesso!')->with('messageType', 'success');
    }

    /**
     * Reativa a conta de um usuário que foi desativado.
     *
     * @param  \App\Models\User  $user  O usuário a ser reativado.
     * @return \Illuminate\Http\RedirectResponse
     */
    public function reactivate(User $user)
    {
        $this->authorize('reactivate', $user);
        // Remove a data de desativação, tornando o usuário ativo novamente.
        $user->update(['deactivated_at' => null]);

        return back()->with('message', 'Usuário reativado com sucesso!')->with('messageType', 'success');
    }
}
