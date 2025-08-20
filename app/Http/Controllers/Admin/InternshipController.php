<?php

namespace App\Http\Controllers\Admin;

use App\Enums\InternshipStatus;
use App\Models\Internship;
use Illuminate\Http\Request;

use App\Http\Controllers\Controller;

class InternshipController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->get('search');
        $status = $request->get('status');

        $query = Internship::with(['advisor', 'course', 'internshipType']);

        // Filtro por nome do estudante
        if ($request->filled('search')) {
            $query->where('student_name', 'like', '%' . $search . '%');
        }

        // Filtro por status
        if ($request->filled('status')) {
            $query->where('status', $status);
        }

        $internships = $query->orderBy('student_name')
            ->paginate(15)
            ->withQueryString();

        $statusOptions = InternshipStatus::options();

        return view('admin.internships.index', compact('internships', 'search', 'status', 'statusOptions'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Internship $internship)
    {
        $internship->load(['advisor', 'course', 'internshipType']);
        $statusOptions = InternshipStatus::options();

        return view('admin.internships.edit', compact('internship', 'statusOptions'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Internship $internship)
    {
        // logica de update

        return redirect()
            ->route('admin.internships.edit', $internship->id)
            ->with('message', 'Estágio atualizado com sucesso!')
            ->with('messageType', 'success');
    }
}
