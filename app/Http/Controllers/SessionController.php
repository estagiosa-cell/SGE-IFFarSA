<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class SessionController extends Controller
{
    public function create()
    {
        return view('auth.login');
    }

    public function store(Request $request)
    {
        $attributes = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (!Auth::attempt($attributes)) {
            throw ValidationException::withMessages([
                'email' => 'As credenciais fornecidas não coincidem com nossos registros.',
            ]);
        }

        $request->session()->regenerate();
        
        return redirect()->intended(route('dashboard'))
            ->with('message', 'Usuário autenticado com sucesso!')
            ->with('messageType', 'success');
    }

    public function destroy(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect(route('login'))
            ->with('message', 'Você foi deslogado com sucesso!')
            ->with('messageType', 'success');
    }
}
