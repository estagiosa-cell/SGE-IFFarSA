<?php

namespace App\Http\Controllers\Admin;

use App\Enums\CourseLevel;
use App\Enums\CourseType;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCourseRequest;
use App\Models\Course;
use App\Models\User;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Course::with('coordinator');

        // Filtro por nome
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // Filtro por nível
        if ($request->filled('level')) {
            $query->where('level', $request->level);
        }

        // Filtro por tipo
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $courses = $query->orderBy('name')->paginate(5)->withQueryString();

        // Dados para os filtros
        $levels = CourseLevel::cases();
        $types = CourseType::cases();

        return view('admin.courses.index', compact('courses', 'levels', 'types'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $levels = CourseLevel::cases();
        $types = CourseType::cases();
        $coordinators = User::coordinators();

        return view('admin.courses.create', compact('levels', 'types', 'coordinators'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCourseRequest $request)
    {
        Course::create($request->validated());

        return redirect()
            ->route('admin.courses.index')
            ->with('success', 'Curso criado com sucesso!');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
