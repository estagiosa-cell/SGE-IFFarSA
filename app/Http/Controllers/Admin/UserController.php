<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Enums\UserRole;
use App\Http\Requests\UserRequest;
use Illuminate\Support\Str;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = User::query();

        $showDeleted = $request->input('show_deleted') === '1';
        if ($showDeleted) {
            $query = $query->onlyTrashed();
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                    ->orWhere('email', 'like', "%$search%");
            });
        }

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        if ($request->filled('status')) {
            if ($request->input('status') === 'active') {
                $query->whereNull('deactivated_at');
            } elseif ($request->input('status') === 'inactive') {
                $query->whereNotNull('deactivated_at');
            }
        }

        $users = $query->latest()->paginate(10)->withQueryString();
        $roles = UserRole::cases();
        return view('admin.users.index', compact('users', 'roles', 'showDeleted'));
    }
    /**
     * Restaura um usuário deletado (soft deleted).
     */
    public function restore($id)
    {
        $user = User::onlyTrashed()->findOrFail($id);
        $user->restore();
        return redirect()->route('admin.users.index', ['show_deleted' => 1])
            ->with('message', 'Usuário restaurado com sucesso!')
            ->with('messageType', 'success');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $roles = UserRole::cases();
        return view('admin.users.create', compact('roles'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(UserRequest $request)
    {
        $data = $request->validated();
        $data['password'] = Str::random(40);
        $user = User::create($data);

        $user->sendWelcomeNotification();

        return redirect()->route('admin.users.index')
            ->with('message', 'Usuário cadastrado com sucesso!')
            ->with('messageType', 'success');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $user = User::findOrFail($id);
        $roles = UserRole::cases();
        return view('admin.users.edit', compact('user', 'roles'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UserRequest $request, string $id)
    {
        $user = User::findOrFail($id);
        $data = $request->validated();

        // Não permitir alteração do próprio papel se for o próprio usuário
        if ($user->id === Auth::id()) {
            unset($data['role']);
        }

        $user->update($data);

        return redirect()->route('admin.users.edit', $user->id)
            ->with('message', 'Usuário atualizado com sucesso!')
            ->with('messageType', 'success');
    }

    /**
     * Importa usuários de um arquivo CSV.
     */
    public function import(Request $request)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user = User::findOrFail($id);

        if ($user->id === Auth::id()) {
            return back()->with('message', 'Você não pode excluir sua própria conta!')->with('messageType', 'danger');
        }

        $user->delete();
        return redirect()->route('admin.users.index')
            ->with('message', 'Usuário excluído com sucesso!')
            ->with('messageType', 'success');
    }

    /**
     * Desativa um usuário.
     */
    public function deactivate(User $user)
    {
        if ($user->id === Auth::id()) {
            return back()->with('message', 'Você não pode desativar sua própria conta!')->with('messageType', 'danger');
        }

        $user->update(['deactivated_at' => now()]);
        return back()->with('message', 'Usuário desativado com sucesso!')->with('messageType', 'success');
    }

    /**
     * Reativa um usuário.
     */
    public function reactivate(User $user)
    {
        $user->update(['deactivated_at' => null]);
        return back()->with('message', 'Usuário reativado com sucesso!')->with('messageType', 'success');
    }
}
