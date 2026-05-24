<?php

namespace App\Http\Controllers\Admin;

use App\Enums\InternshipStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateInternshipRequest;
use App\Models\Company;
use App\Models\Internship;
use App\Utils\SearchHelper;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

/**
 * Controlador para gerenciar os Estágios no painel administrativo.
 *
 * Este controlador lida com a listagem, edição, atualização, exclusão
 * e restauração de estágios, além de fornecer endpoints para dados auxiliares.
 */
class InternshipController extends Controller
{
    use AuthorizesRequests;

    /**
     * Exibe uma lista de estágios com filtros e ordenação.
     *
     * @param  \Illuminate\Http\Request  $request  A requisição HTTP com os parâmetros de filtro.
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Internship::class);

        $search = $request->get('search');
        $status = $request->get('status');
        $courseId = $request->get('course_id');
        $endDateFrom = $request->get('end_date_from');
        $endDateTo = $request->get('end_date_to');
        $orderBy = $request->get('order_by', 'status_priority'); // Padrão: ordenação por prioridade de status

        // Inicia a query com o carregamento antecipado de relacionamentos para otimização.
        $query = Internship::with(['advisor', 'course']);

        // Verifica se o filtro 'show_deleted' está ativo para incluir estágios removidos (soft delete).
        $showDeleted = $request->input('show_deleted') === '1';
        if ($showDeleted) {
            $query = $query->onlyTrashed();
        }

        // Aplica o filtro de busca por nome do estudante, se presente.
        if ($request->filled('search')) {
            SearchHelper::searchInField($query, $search, 'student_name');
        }

        // Aplica o filtro por status do estágio, se presente.
        if ($request->filled('status')) {
            $query->where('status', $status);
        }

        // Aplica o filtro por curso, se presente.
        if ($request->filled('course_id')) {
            $query->where('course_id', $courseId);
        }

        // Aplica o filtro por data de término (de), se presente.
        if ($request->filled('end_date_from')) {
            $query->whereDate('end_date', '>=', $endDateFrom);
        }

        // Aplica o filtro por data de término (até), se presente.
        if ($request->filled('end_date_to')) {
            $query->whereDate('end_date', '<=', $endDateTo);
        }

        // Define uma ordem de prioridade para os status dos estágios a partir do Enum,
        // garantindo que os pendentes e em andamento apareçam primeiro.
        $statusOrderSql = InternshipStatus::orderSql();

        // Aplica a ordenação baseada no parâmetro order_by
        switch ($orderBy) {
            case 'name_asc':
                $query->orderBy('student_name', 'asc');
                break;
            case 'name_desc':
                $query->orderBy('student_name', 'desc');
                break;
            case 'start_date_asc':
                $query->orderBy('start_date', 'asc');
                break;
            case 'start_date_desc':
                $query->orderBy('start_date', 'desc');
                break;
            case 'end_date_asc':
                $query->orderBy('end_date', 'asc');
                break;
            case 'end_date_desc':
                $query->orderBy('end_date', 'desc');
                break;
            case 'status_priority':
            default:
                $query->orderByRaw($statusOrderSql)
                    ->latest('end_date')
                    ->latest('updated_at');
                break;
        }

        // Executa a query e pagina os resultados.
        $internships = $query->paginate(100);

        // Obtém as opções de status para o dropdown de filtro.
        $statusOptions = InternshipStatus::options();

        // Obtém todos os cursos para o filtro.
        $courses = \App\Models\Course::orderBy('name')->get(['id', 'name']);

        return view('admin.internships.index', compact('internships', 'search', 'status', 'statusOptions', 'showDeleted', 'courses', 'courseId', 'endDateFrom', 'endDateTo', 'orderBy'));
    }

    /**
     * Exibe o formulário para editar um estágio específico.
     *
     * @param  \App\Models\Internship  $internship  A instância do estágio injetada pelo Route Model Binding.
     * @return \Illuminate\View\View
     */
    public function edit(Internship $internship)
    {
        $this->authorize('update', $internship);

        // Carrega os relacionamentos para serem usados na view.
        $internship->load(['advisor', 'course']);
        $statusOptions = InternshipStatus::options();

        // Busca orientadores disponíveis (usuários com papel de orientador ou coordenador) que estão ativos.
        $advisors = \App\Models\User::whereIn('role', ['orientador', 'coordenador'])
            ->whereNull('deactivated_at')
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('admin.internships.edit', compact('internship', 'statusOptions', 'advisors'));
    }

    /**
     * Atualiza um estágio específico no banco de dados.
     *
     * @param  \App\Http\Requests\UpdateInternshipRequest  $request  A requisição HTTP com os dados do formulário.
     * @param  \App\Models\Internship  $internship  A instância do estágio a ser atualizada.
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(UpdateInternshipRequest $request, Internship $internship)
    {
        $validatedData = $request->validated();

        try {
            // Recalcula a nota final da avaliação com base nos critérios preenchidos.
            $validatedData['evaluation_grade'] = $internship->calculateEvaluationGrade($validatedData);

            // Atualiza o estágio com os dados validados e a nota calculada.
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
     * Busca empresas pelo CPF/CNPJ.
     *
     * Este método é usado como um endpoint de API (geralmente via AJAX)
     * para preencher dados da empresa no formulário de estágio.
     *
     * @param  \Illuminate\Http\Request  $request  A requisição contendo o identificador.
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCompanies(Request $request)
    {
        $this->authorize('viewAny', Internship::class);

        $identificador = $request->get('identificador');

        if (! $identificador) {
            return response()->json([]);
        }

        // Busca empresas que correspondem ao CPF/CNPJ fornecido.
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
     * Remove o estágio especificado do sistema (soft delete).
     *
     * @param  \App\Models\Internship  $internship  A instância do estágio a ser excluída.
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Internship $internship)
    {
        $this->authorize('delete', $internship);

        $internship->delete();

        return redirect()->route('admin.internships.index')
            ->with('message', 'Estágio excluído com sucesso!')
            ->with('messageType', 'success');
    }

    /**
     * Restaura um estágio que foi removido via soft delete.
     *
     * @param  string  $id  O ID do estágio a ser restaurado.
     * @return \Illuminate\Http\RedirectResponse
     */
    public function restore($id)
    {
        // Busca o estágio apenas na lixeira (onlyTrashed).
        $internship = Internship::onlyTrashed()->findOrFail($id);

        $this->authorize('restore', $internship);

        $internship->restore();

        // Redireciona de volta para a lista de estágios excluídos.
        return redirect()->route('admin.internships.index', ['show_deleted' => 1])
            ->with('message', 'Estágio restaurado com sucesso!')
            ->with('messageType', 'success');
    }
}
