<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SessionController extends Controller
{
    public function create()
    {
        return view('auth.login');
    }

    public function store()
    {
        // Lógica para autenticar o usuário
    }

    public function destroy()
    {
        // Lógica para encerrar a sessão do usuário
    }
}
