<?php

namespace App\Http\Controllers\Admin;

use App\Enums\InternshipStatus;
use App\Models\Internship;
use App\Models\Company;
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

        $query = Internship::with(['advisor', 'course']);

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
        $internship->load(['advisor', 'course']);
        $statusOptions = InternshipStatus::options();

        // Busca empresas com o mesmo CNPJ da empresa atual do estágio
        $companiesWithSameCnpj = [];
        if ($internship->company_legal_identifier) {
            $companiesWithSameCnpj = Company::where('legal_identifier', $internship->company_legal_identifier)
                ->get(['id', 'name']);
        }

        return view('admin.internships.edit', compact('internship', 'statusOptions', 'companiesWithSameCnpj'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Internship $internship)
    {
        $validatedData = $request->validate([
            // Informações do Sistema
            'status' => 'required|in:' . implode(',', array_keys(InternshipStatus::options())),
            'notes' => 'nullable|string',

            // Dados do Aluno
            'student_name' => 'required|string|max:255',
            'student_email' => 'required|email|max:255',
            'student_registration_number' => 'required|string|max:50',
            'student_year_semester' => 'required|string|max:20',
            'student_birth_date' => 'required|date',
            'student_rg' => 'required|string|max:20',
            'student_rg_issuer' => 'required|string|max:50',
            'student_rg_issue_date' => 'required|date',
            'student_cpf' => 'required|string|max:14',
            'student_phone' => 'required|string|max:20',

            // Endereço do Aluno
            'student_address_street' => 'required|string|max:255',
            'student_address_number' => 'required|string|max:20',
            'student_address_neighborhood' => 'required|string|max:100',
            'student_address_city' => 'required|string|max:100',
            'student_address_state' => 'required|string|size:2',
            'student_address_zip' => 'required|string|max:10',

            // Dados do Responsável Legal
            'legal_guardian_name' => 'nullable|string|max:255',
            'legal_guardian_cpf' => 'nullable|string|max:14',
            'legal_guardian_kinship' => 'nullable|string|max:50',
            'legal_guardian_email' => 'nullable|email|max:255',

            // Dados da Empresa/Parte Concedente
            'company_legal_identifier' => 'required|string|max:20',
            'company_name' => 'required|string|max:255',
            'company_phone' => 'nullable|string|max:20',
            'company_email' => 'nullable|email|max:255',
            'company_representative_name' => 'required|string|max:255',
            'company_representative_role' => 'required|string|max:100',
            'field_of_activity' => 'required|string|max:255',

            // Endereço da Empresa
            'company_address_street' => 'nullable|string|max:255',
            'company_address_number' => 'nullable|string|max:20',
            'company_address_neighborhood' => 'nullable|string|max:100',
            'company_address_city' => 'nullable|string|max:100',
            'company_address_state' => 'nullable|string|size:2',
            'company_address_zip' => 'nullable|string|max:10',

            // Dados do Supervisor
            'supervisor_name' => 'required|string|max:255',
            'supervisor_phone' => 'nullable|string|max:20',
            'supervisor_email' => 'nullable|email|max:255',
            'supervisor_role' => 'required|string|max:100',

            // Dados do Estágio
            'internship_type_name' => 'required|string|max:100',
            'internship_sector' => 'nullable|string|max:100',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'required_hours' => 'required|integer|min:1',
            'activities' => 'required|string',
            'evaluation_grade' => 'nullable|numeric|min:0|max:100',
        ]);

        try {
            $internship->update($validatedData);

            return redirect()
                ->route('admin.internships.edit', $internship->id)
                ->with('message', 'Estágio atualizado com sucesso!')
                ->with('messageType', 'success');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('message', 'Erro ao atualizar estágio: ' . $e->getMessage())
                ->with('messageType', 'error');
        }
    }

    /**
     * Get companies by CNPJ
     */
    public function getCompaniesByCnpj(Request $request)
    {
        $cnpj = $request->get('cnpj');

        if (!$cnpj) {
            return response()->json([]);
        }

        $companies = Company::where('legal_identifier', $cnpj)
            ->get([
                'id',
                'name',
                'representative_name',
                'representative_role',
                'phone',
                'email',
                'field_of_activity',
                'address_street',
                'address_number',
                'address_neighborhood',
                'address_city',
                'address_state',
                'address_zip'
            ]);

        return response()->json($companies);
    }
}
