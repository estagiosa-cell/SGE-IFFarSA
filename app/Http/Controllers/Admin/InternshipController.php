<?php

namespace App\Http\Controllers\Admin;

use App\Enums\InternshipStatus;
use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Internship;
use App\Utils\SearchHelper;
use Illuminate\Http\Request;

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

        $showDeleted = $request->input('show_deleted') === '1';
        if ($showDeleted) {
            $query = $query->onlyTrashed();
        }

        // Filtro por nome do estudante
        if ($request->filled('search')) {
            SearchHelper::searchInField($query, $search, 'student_name');
        }

        // Filtro por status
        if ($request->filled('status')) {
            $query->where('status', $status);
        }

        // Raw SQL para ordenação por prioridade de status
        $statusOrderSql = "
            CASE status
                WHEN 'Pendente' THEN 1
                WHEN 'Aguardando Assinatura' THEN 2
                WHEN 'Em Andamento' THEN 3
                WHEN 'Concluído' THEN 4
                WHEN 'Cancelado' THEN 5
                ELSE 99
            END
        ";

        $internships = $query->orderByRaw($statusOrderSql)
            ->latest('end_date')
            ->latest('updated_at')
            ->get();

        $statusOptions = InternshipStatus::options();

        return view('admin.internships.index', compact('internships', 'search', 'status', 'statusOptions', 'showDeleted'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Internship $internship)
    {
        $internship->load(['advisor', 'course']);
        $statusOptions = InternshipStatus::options();

        // Busca orientadores disponíveis (usuários com role orientador ou coordenador)
        $advisors = \App\Models\User::whereIn('role', ['orientador', 'coordenador'])
            ->whereNull('deactivated_at')
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('admin.internships.edit', compact('internship', 'statusOptions', 'advisors'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Internship $internship)
    {
        $validatedData = $request->validate([
            // Informações do Sistema
            'advisor_id' => 'required|exists:users,id',
            'status' => 'required|in:'.implode(',', array_keys(InternshipStatus::options())),
            'notes' => 'nullable|string',

            // Dados do Aluno
            'student_name' => 'required|string|max:255',
            'student_email' => 'required|email|max:255',
            'student_registration_number' => 'required|string|max:50',
            'student_year_semester' => 'required|string|max:20',
            'student_birth_date' => 'required|date',
            'student_is_adult' => 'nullable|boolean',
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
            'legal_guardian_name' => 'nullable|required_if:student_is_adult,0|string|max:255',
            'legal_guardian_cpf' => 'nullable|required_if:student_is_adult,0|string|max:14',
            'legal_guardian_kinship' => 'nullable|required_if:student_is_adult,0|string|max:50',
            'legal_guardian_email' => 'nullable|email|max:255',

            // Dados da Empresa/Parte Concedente
            'company_legal_identifier' => 'required|string|max:20',
            'company_name' => 'nullable|string|max:255',
            'company_phone' => 'nullable|string|max:20',
            'company_email' => 'nullable|email|max:255',
            'company_representative_name' => 'nullable|string|max:255',
            'company_representative_role' => 'nullable|string|max:100',
            'field_of_activity' => 'nullable|string|max:255',

            // Endereço da Empresa
            'company_address_street' => 'nullable|string|max:255',
            'company_address_number' => 'nullable|string|max:20',
            'company_address_neighborhood' => 'nullable|string|max:100',
            'company_address_city' => 'nullable|string|max:100',
            'company_address_state' => 'nullable|string|size:2',
            'company_address_zip' => 'nullable|string|max:10',

            // Informações Adicionais da Empresa
            'professional_council' => 'nullable|string|max:100',
            'council_registration_number' => 'nullable|string|max:50',
            'process_number' => 'nullable|string|max:100',

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

            // Remuneração
            'is_remunerated' => 'nullable|boolean',
            'grant_value' => 'nullable|numeric|min:0',
            'transportation_allowance' => 'nullable|numeric|min:0',
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
                ->with('message', 'Erro ao atualizar estágio: '.$e->getMessage())
                ->with('messageType', 'error');
        }
    }

    /**
     * Get companies by CNPJ
     */
    public function getCompanies(Request $request)
    {
        $identificador = $request->get('identificador');

        if (! $identificador) {
            return response()->json([]);
        }

        $companies = Company::where('legal_identifier', $identificador)
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
                'address_zip',
                'professional_council',
                'council_registration_number',
                'process_number',
            ]);

        return response()->json($companies);
    }

    /**
     * Remove o estágio especificado (soft delete).
     */
    public function destroy(Internship $internship)
    {
        $internship->delete();

        return redirect()->route('admin.internships.index')
            ->with('message', 'Estágio excluído com sucesso!')
            ->with('messageType', 'success');
    }

    /**
     * Restaura um estágio deletado (soft deleted).
     */
    public function restore($id)
    {
        $internship = Internship::onlyTrashed()->findOrFail($id);
        $internship->restore();

        return redirect()->route('admin.internships.index', ['show_deleted' => 1])
            ->with('message', 'Estágio restaurado com sucesso!')
            ->with('messageType', 'success');
    }
}
