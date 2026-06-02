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
        $registration = $request->get('registration');
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

        $query->applyStandardFilters($request, [
            'allow_course_filter' => true,
            'skip_name_search' => true,
        ]);

        // Aplica a ordenação baseada no parâmetro order_by usando o scope do model
        $query->applyStandardOrdering($orderBy);

        $internships = SearchHelper::searchAndPaginate(
            $query,
            $request,
            $search,
            'student_name'
        );

        // Obtém as opções de status para o dropdown de filtro.
        $statusOptions = InternshipStatus::options();

        // Obtém todos os cursos para o filtro.
        $courses = \App\Models\Course::orderBy('name')->get(['id', 'name']);

        $activeFilters = collect([
            $search,
            $registration,
            $status,
            $courseId,
            $endDateFrom,
            $endDateTo,
        ]);
        if ($orderBy && $orderBy !== 'status_priority') {
            $activeFilters->push($orderBy);
        }
        $activeFiltersCount = $activeFilters->filter(fn ($v) => filled($v))->count();

        return view('admin.internships.index', compact('internships', 'search', 'status', 'registration', 'statusOptions', 'showDeleted', 'courses', 'courseId', 'endDateFrom', 'endDateTo', 'orderBy', 'activeFiltersCount'));
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

    /**
     * Recalcula a data de término do estágio com base nas horas restantes.
     *
     * @param  \Illuminate\Http\Request  $request  A requisição com calc_start_date e remaining_hours.
     * @param  \App\Models\Internship  $internship  A instância do estágio.
     * @return \Illuminate\Http\RedirectResponse
     */
    public function recalculateEndDate(Request $request, Internship $internship)
    {
        $this->authorize('update', $internship);

        $request->validate([
            'calc_start_date' => 'required|date',
            'remaining_hours' => 'required|integer|min:1|max:'.$internship->required_hours,
        ]);

        try {
            $weeklyHours = [
                0 => (int) $internship->hours_sunday,
                1 => (int) $internship->hours_monday,
                2 => (int) $internship->hours_tuesday,
                3 => (int) $internship->hours_wednesday,
                4 => (int) $internship->hours_thursday,
                5 => (int) $internship->hours_friday,
                6 => (int) $internship->hours_saturday,
            ];

            $startDate = \Carbon\Carbon::parse($request->calc_start_date);
            $newEndDate = \App\Utils\InternshipEndDate::calculateInternshipEndDate(
                $startDate,
                $weeklyHours,
                (int) $request->remaining_hours
            );

            $internship->end_date = $newEndDate;
            $internship->save();

            return redirect()
                ->route('admin.internships.edit', $internship->id)
                ->with('message', 'Data de término recalculada com sucesso! Nova data: '.$newEndDate->format('d/m/Y'))
                ->with('messageType', 'success');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('message', 'Erro ao recalcular data de término: '.$e->getMessage())
                ->with('messageType', 'error');
        }
    }
}

