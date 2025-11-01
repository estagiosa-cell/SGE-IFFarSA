<?php

namespace App\Http\Controllers\Admin;

use App\Enums\InternshipStatus;
use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Internship;
use App\Utils\SearchHelper;
use Illuminate\Http\Request;

/**
 * Controlador para gerenciar os Estágios no painel administrativo.
 *
 * Este controlador lida com a listagem, edição, atualização, exclusão
 * e restauração de estágios, além de fornecer endpoints para dados auxiliares.
 */
class InternshipController extends Controller
{
    /**
     * Exibe uma lista de estágios com filtros e ordenação.
     *
     * @param  \Illuminate\Http\Request  $request  A requisição HTTP com os parâmetros de filtro.
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $search = $request->get('search');
        $status = $request->get('status');

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

        // Define uma ordem de prioridade para os status dos estágios,
        // garantindo que os pendentes e em andamento apareçam primeiro.
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

        // Executa a query com a ordenação customizada e pagina os resultados.
        $internships = $query->orderByRaw($statusOrderSql)
            ->latest('end_date')
            ->latest('updated_at')
            ->paginate(100);

        // Obtém as opções de status para o dropdown de filtro.
        $statusOptions = InternshipStatus::options();

        return view('admin.internships.index', compact('internships', 'search', 'status', 'statusOptions', 'showDeleted'));
    }

    /**
     * Exibe o formulário para editar um estágio específico.
     *
     * @param  \App\Models\Internship  $internship  A instância do estágio injetada pelo Route Model Binding.
     * @return \Illuminate\View\View
     */
    public function edit(Internship $internship)
    {
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
     * @param  \Illuminate\Http\Request  $request  A requisição HTTP com os dados do formulário.
     * @param  \App\Models\Internship  $internship  A instância do estágio a ser atualizada.
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Internship $internship)
    {
        // Valida todos os campos do formulário de edição de estágio.
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

            // Dados do Responsável Legal (obrigatório se o aluno for menor de idade)
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
            'internship_type_weight' => 'nullable|integer|min:1|max:10',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'required_hours' => 'required|integer|min:1',
            'activities' => 'required|string',

            // Remuneração
            'is_remunerated' => 'nullable|boolean',
            'grant_value' => 'nullable|numeric|min:0',
            'transportation_allowance' => 'nullable|numeric|min:0',

            // Avaliação do Supervisor
            'evaluation_supervisor_name' => 'nullable|string|max:255',
            'evaluation_supervisor_email' => 'nullable|email|max:255',
            'evaluation_has_academic_background' => 'nullable|string|max:255',
            'evaluation_completed_workload' => 'nullable|string|max:255',
            'evaluation_training_course' => 'nullable|string|max:255',
            'evaluation_education_level' => 'nullable|string|max:255',
            'evaluation_job_role' => 'nullable|string|max:255',
            'evaluation_experience_time' => 'nullable|string|max:255',
            'evaluation_performance' => 'nullable|string|max:255',
            'evaluation_comprehension' => 'nullable|string|max:255',
            'evaluation_technical_knowledge' => 'nullable|string|max:255',
            'evaluation_organization' => 'nullable|string|max:255',
            'evaluation_initiative' => 'nullable|string|max:255',
            'evaluation_attendance' => 'nullable|string|max:255',
            'evaluation_discipline' => 'nullable|string|max:255',
            'evaluation_sociability' => 'nullable|string|max:255',
            'evaluation_cooperation' => 'nullable|string|max:255',
            'evaluation_responsibility' => 'nullable|string|max:255',
            'evaluation_considerations' => 'nullable|string',
            'evaluation_suggestions_to_institution' => 'nullable|string',
            'evaluation_performance_issues' => 'nullable|string',
            'evaluation_other_observations' => 'nullable|string',
            'evaluation_grade' => 'nullable|numeric|min:0|max:20',

            // Valores customizáveis para conceitos (numéricos) - agora obrigatórios
            'great_value' => 'required|numeric|min:0',
            'very_good_value' => 'required|numeric|min:0',
            'good_value' => 'required|numeric|min:0',
            'satisfactory_value' => 'required|numeric|min:0',
            'unsatisfactory_value' => 'required|numeric|min:0',
        ]);

        try {
            // Recalcula a nota final da avaliação com base nos critérios preenchidos.
            $validatedData['evaluation_grade'] = $this->calculateEvaluationGrade($validatedData, $internship);

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
        $internship->restore();

        // Redireciona de volta para a lista de estágios excluídos.
        return redirect()->route('admin.internships.index', ['show_deleted' => 1])
            ->with('message', 'Estágio restaurado com sucesso!')
            ->with('messageType', 'success');
    }

    /**
     * Calcula a nota final da avaliação com base nos 10 critérios de desempenho.
     *
     * @param  array  $data  Os dados validados da requisição.
     * @param  \App\Models\Internship  $internship  A instância do estágio.
     * @return float A média das notas dos critérios preenchidos.
     */
    private function calculateEvaluationGrade(array $data, Internship $internship): float
    {
        // Lista dos campos que representam os critérios de avaliação.
        $criteria = [
            'evaluation_performance',
            'evaluation_comprehension',
            'evaluation_technical_knowledge',
            'evaluation_organization',
            'evaluation_initiative',
            'evaluation_attendance',
            'evaluation_discipline',
            'evaluation_sociability',
            'evaluation_cooperation',
            'evaluation_responsibility',
        ];

        $totalScore = 0.0;
        $count = 0;

        // Itera sobre cada critério para somar as notas.
        foreach ($criteria as $criterion) {
            $text = $data[$criterion] ?? null;
            if (! empty($text)) {
                // Converte o conceito textual (ex: "Bom") para um valor numérico.
                $totalScore += $this->getNumericValue($text, $data, $internship);
                $count++;
            }
        }

        // Retorna a média ou 0.0 se nenhum critério foi preenchido.
        return $count > 0 ? $totalScore / $count : 0.0;
    }

    /**
     * Converte um conceito textual de avaliação (ex: "Ótimo") para seu valor numérico correspondente.
     *
     * A ordem de prioridade para obter o valor é:
     * 1. Valores customizados enviados na requisição atual (ex: `great_value` no formulário).
     * 2. Valores customizados já salvos no registro do estágio.
     * 3. Valores padrão definidos no código.
     *
     * @param  string|null  $value  O conceito textual (ex: "Ótimo", "Bom").
     * @param  array  $data  Os dados da requisição atual, que podem conter overrides.
     * @param  \App\Models\Internship|null  $internship  O estágio, para buscar valores salvos.
     * @return float O valor numérico correspondente.
     */
    private function getNumericValue(?string $value, array $data = [], ?Internship $internship = null): float
    {
        // Valores padrão caso nenhuma customização seja encontrada.
        $defaults = [
            'Ótimo' => 2.0,
            'Muito Bom' => 1.5,
            'Bom' => 1.0,
            'Satisfatório' => 0.5,
            'Insatisfatório' => 0.0,
        ];

        if (empty($value)) {
            return 0.0;
        }

        // Mapeia o conceito textual para a chave do campo no banco/requisição.
        $map = [
            'Ótimo' => 'great_value',
            'Muito Bom' => 'very_good_value',
            'Bom' => 'good_value',
            'Satisfatório' => 'satisfactory_value',
            'Insatisfatório' => 'unsatisfactory_value',
        ];

        $key = $map[$value] ?? null;

        // Prioridade 1: Verifica se há um valor customizado na requisição atual.
        if ($key && isset($data[$key]) && is_numeric($data[$key])) {
            return (float) $data[$key];
        }

        // Prioridade 2: Verifica se há um valor customizado salvo no estágio.
        if ($key && $internship && isset($internship->{$key})) {
            return (float) $internship->{$key};
        }

        // Prioridade 3: Usa o valor padrão.
        return $defaults[$value] ?? 0.0;
    }
}
