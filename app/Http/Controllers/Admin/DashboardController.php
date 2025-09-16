<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Internship;
use App\Models\User;
use App\Models\Course;
use App\Models\Company;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        // logica de busca de dados para o dashboard
        
        return view('admin.dashboard');
    }
}
