<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\UserRequest;
use App\Models\User;
use App\Utils\SearchHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

/**
 * Controlador para gerenciar usuários no painel administrativo.
 *
 * Este controlador lida com a listagem, criação, edição, exclusão,
 * importação e outras operações relacionadas aos usuários do sistema.
 */
class UserController extends Controller
{
    /**
     * Exibe uma lista de usuários com filtros.
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        // Inicia a query para buscar usuários.
        $query = User::query();

        // Verifica se o filtro 'show_deleted' está ativo para incluir usuários removidos (soft delete).
        $showDeleted = $request->input('show_deleted') === '1';
        if ($showDeleted) {
            $query = $query->onlyTrashed();
        }

        // Aplica o filtro de busca por nome ou e-mail, se presente.
        if ($request->filled('search')) {
            $search = $request->input('search');
            SearchHelper::searchInFields($query, $search, ['name', 'email']);
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

        // Pagina os resultados e busca todos os papéis para o formulário de filtro.
        $users = $query->latest()->paginate(100);
        $roles = UserRole::cases();

        // Retorna a view com os dados.
        return view('admin.users.index', compact('users', 'roles', 'showDeleted'));
    }

    /**
     * Exibe o formulário para criar um novo usuário.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
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
    public function store(UserRequest $request)
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
     * @param  string  $id  O ID do usuário.
     * @return \Illuminate\View\View
     */
    public function edit(string $id)
    {
        // Encontra o usuário pelo ID ou falha.
        $user = User::findOrFail($id);
        
        // Busca todos os papéis de usuário para o formulário.
        $roles = UserRole::cases();

        // Retorna a view de edição com os dados do usuário e os papéis.
        return view('admin.users.edit', compact('user', 'roles'));
    }

    /**
     * Atualiza um usuário específico no banco de dados.
     *
     * @param  string  $id  O ID do usuário.
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(UserRequest $request, string $id)
    {
        // Encontra o usuário pelo ID ou falha.
        $user = User::findOrFail($id);
        // Obtém os dados validados da requisição.
        $data = $request->validated();

        // Regra de negócio: Não permitir que o usuário altere o próprio papel.
        if ($user->id === Auth::id() && $data['role'] !== $user->role->value) {
            return back()
                ->with('message', 'Não é possível alterar o próprio papel!')
                ->with('messageType', 'danger');
        }

        // Regra de negócio: Impedir a alteração do papel de um coordenador que possui cursos associados.
        if ($user->role === UserRole::COORDENADOR && $user->coordinatedCourses()->exists() && $data['role'] !== UserRole::COORDENADOR->value) {
            return back()
                ->with('message', 'Não é possível alterar o papel de um coordenador com cursos atrelados!')
                ->with('messageType', 'danger');
        }

        // Atualiza os dados do usuário.
        $user->update($data);

        // Redireciona para a página de edição com uma mensagem de sucesso.
        return redirect()->route('admin.users.edit', $user->id)
            ->with('message', 'Usuário atualizado com sucesso!')
            ->with('messageType', 'success');
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
        // Valida o arquivo enviado.
        $request->validate([
            'file' => 'required|file|mimes:csv|max:10240', // max 10MB
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

        // Itera sobre as linhas e cria os novos usuários.
        foreach ($rows as $row) {
            User::create([
                'name' => $row[0],
                'email' => $row[1],
                'role' => UserRole::ORIENTADOR, // Papel padrão para usuários importados.
                'password' => bcrypt(Str::random(40)), // Senha aleatória, já que o login é via SSO.
            ]);
        }

        return redirect()->route('admin.users.index')
            ->with('message', 'Usuários importados com sucesso!')
            ->with('messageType', 'success');
    }

    /**
     * Remove um usuário do sistema (soft delete).
     *
     * @param  string  $id  O ID do usuário a ser excluído.
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(string $id)
    {
        $user = User::findOrFail($id);

        // Impede que o usuário exclua a própria conta.
        if ($user->id === Auth::id()) {
            return back()->with('message', 'Você não pode excluir sua própria conta!')->with('messageType', 'danger');
        }

        $user->delete();

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
        // Impede que o usuário desative a própria conta.
        if ($user->id === Auth::id()) {
            return back()->with('message', 'Você não pode desativar sua própria conta!')->with('messageType', 'danger');
        }

        // Define a data de desativação para o momento atual.
        $user->update(['deactivated_at' => now()]);

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
        // Remove a data de desativação, tornando o usuário ativo novamente.
        $user->update(['deactivated_at' => null]);

        return back()->with('message', 'Usuário reativado com sucesso!')->with('messageType', 'success');
    }
}
