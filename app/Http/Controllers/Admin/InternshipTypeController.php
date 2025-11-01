<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\InternshipTypeRequest;
use App\Models\Course;
use App\Models\InternshipType;
use App\Utils\SearchHelper;
use Illuminate\Http\Request;

/**
 * Controlador para gerenciar os tipos de estágio.
 *
 * Este controlador lida com as operações de CRUD (Criar, Ler, Atualizar, Deletar)
 * para os tipos de estágio, além de funcionalidades como busca, filtragem e
 * restauração de registros excluídos (soft delete).
 */
class InternshipTypeController extends Controller
{
    /**
     * Exibe a lista de tipos de estágio com filtros e paginação.
     *
     * @param  \Illuminate\Http\Request  $request  A requisição HTTP, contendo possíveis filtros.
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        // Inicia a query com o relacionamento do curso para otimização.
        $query = InternshipType::with('course');

        // Verifica se o usuário deseja ver os registros excluídos (soft deleted).
        $showDeleted = $request->input('show_deleted') === '1';
        if ($showDeleted) {
            $query = $query->onlyTrashed();
        }

        // Aplica o filtro de busca por nome, se presente na requisição.
        if ($request->filled('search')) {
            SearchHelper::searchInField($query, $request->search, 'name');
        }

        // Aplica o filtro por curso, se presente na requisição.
        if ($request->filled('course_id')) {
            $query->where('course_id', $request->course_id);
        }

        // Ordena os resultados por nome e pagina o resultado.
        $internshipTypes = $query->orderBy('name')->paginate(100);

        // Carrega os cursos para preencher o dropdown de filtro.
        $courses = Course::orderBy('name')->get();

        // Retorna a view com os dados necessários.
        return view('admin.internship_types.index', compact('internshipTypes', 'courses', 'showDeleted'));
    }

    /**
     * Exibe o formulário para criar um novo tipo de estágio.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        // Carrega todos os cursos para o dropdown de seleção.
        $courses = Course::all();

        return view('admin.internship_types.create', compact('courses'));
    }

    /**
     * Armazena um novo tipo de estágio no banco de dados.
     *
     * @param  \App\Http\Requests\InternshipTypeRequest  $request  A requisição validada.
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(InternshipTypeRequest $request)
    {
        // Obtém os dados validados da requisição.
        $data = $request->validated();
        // Cria o novo tipo de estágio.
        InternshipType::create($data);

        // Redireciona para a lista com uma mensagem de sucesso.
        return redirect()->route('admin.internship-types.index')
            ->with('message', 'Tipo de estágio cadastrado com sucesso!')
            ->with('messageType', 'success');
    }

    /**
     * Exibe o formulário para editar um tipo de estágio específico.
     *
     * @param  string  $id  O ID do tipo de estágio a ser editado.
     * @return \Illuminate\View\View
     */
    public function edit(string $id)
    {
        // Encontra o tipo de estágio ou falha se não existir.
        $internshipType = InternshipType::findOrFail($id);
        // Carrega todos os cursos para o dropdown de seleção.
        $courses = Course::all();

        return view('admin.internship_types.edit', compact('internshipType', 'courses'));
    }

    /**
     * Atualiza um tipo de estágio específico no banco de dados.
     *
     * @param  \App\Http\Requests\InternshipTypeRequest  $request  A requisição validada.
     * @param  string  $id  O ID do tipo de estágio a ser atualizado.
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(InternshipTypeRequest $request, string $id)
    {
        // Encontra o tipo de estágio ou falha se não existir.
        $internshipType = InternshipType::findOrFail($id);
        // Atualiza o tipo de estágio com os dados validados.
        $internshipType->update($request->validated());

        // Redireciona para o formulário de edição com uma mensagem de sucesso.
        return redirect()->route('admin.internship-types.edit', $id)
            ->with('message', 'Tipo de estágio atualizado com sucesso!')
            ->with('messageType', 'success');
    }

    /**
     * Remove o tipo de estágio especificado (soft delete).
     *
     * @param  string  $id  O ID do tipo de estágio a ser excluído.
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(string $id)
    {
        // Encontra o tipo de estágio ou falha se não existir.
        $internshipType = InternshipType::findOrFail($id);
        // Realiza o soft delete.
        $internshipType->delete();

        // Redireciona para a lista com uma mensagem de sucesso.
        return redirect()->route('admin.internship-types.index')
            ->with('message', 'Tipo de estágio excluído com sucesso!')
            ->with('messageType', 'success');
    }

    /**
     * Restaura um tipo de estágio que foi excluído (soft deleted).
     *
     * @param  string  $id  O ID do tipo de estágio a ser restaurado.
     * @return \Illuminate\Http\RedirectResponse
     */
    public function restore($id)
    {
        // Encontra o tipo de estágio na lixeira ou falha se não existir.
        $internshipType = InternshipType::onlyTrashed()->findOrFail($id);
        // Restaura o registro.
        $internshipType->restore();

        // Redireciona para a lista de excluídos com uma mensagem de sucesso.
        return redirect()->route('admin.internship-types.index', ['show_deleted' => 1])
            ->with('message', 'Tipo de estágio restaurado com sucesso!')
            ->with('messageType', 'success');
    }
}
