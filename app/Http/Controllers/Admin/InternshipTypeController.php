<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreInternshipTypeRequest;
use App\Http\Requests\UpdateInternshipTypeRequest;
use App\Models\Course;
use App\Models\InternshipType;
use App\Utils\SearchHelper;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Controlador para gerenciar os tipos de estágio.
 *
 * Este controlador lida com as operações de CRUD (Criar, Ler, Atualizar, Deletar)
 * para os tipos de estágio, além de funcionalidades como busca, filtragem e
 * restauração de registros excluídos (soft delete).
 */
class InternshipTypeController extends Controller
{
    use AuthorizesRequests;

    /**
     * Exibe a lista de tipos de estágio com filtros e paginação.
     *
     * @param  \Illuminate\Http\Request  $request  A requisição HTTP, contendo possíveis filtros.
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', InternshipType::class);

        // Inicia a query com o relacionamento do curso para otimização.
        $query = InternshipType::with('course');

        // Verifica se o usuário deseja ver os registros excluídos (soft deleted).
        $showDeleted = $request->input('show_deleted') === '1';
        if ($showDeleted) {
            $query = $query->onlyTrashed();
        }

        // Aplica o filtro por curso, se presente na requisição.
        if ($request->filled('course_id')) {
            $query->where('course_id', $request->course_id);
        }

        $perPage = 100;
        $orderedQuery = $query->orderBy('name');
        $hasSearch = $request->filled('search');
        $useUnaccent = DB::getDriverName() === 'pgsql';

        if ($hasSearch && $useUnaccent) {
            $search = $request->input('search');
            SearchHelper::applyUnaccentSearch($orderedQuery, $search, 'name');
            $internshipTypes = $orderedQuery->paginate($perPage);
        } elseif ($hasSearch) {
            $search = $request->input('search');
            $internshipTypes = $orderedQuery->get();
            $internshipTypes = SearchHelper::filterCollectionByNormalizedWords($internshipTypes, $search, 'name');
            $internshipTypes = SearchHelper::paginateCollection($internshipTypes, $perPage, $request);
        } else {
            $internshipTypes = $orderedQuery->paginate($perPage);
        }

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
        $this->authorize('create', InternshipType::class);

        // Carrega todos os cursos para o dropdown de seleção.
        $courses = Course::all();

        return view('admin.internship_types.create', compact('courses'));
    }

    /**
     * Armazena um novo tipo de estágio no banco de dados.
     *
     * @param  \App\Http\Requests\StoreInternshipTypeRequest  $request  A requisição validada.
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(StoreInternshipTypeRequest $request)
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
     * @param  \App\Models\InternshipType  $internshipType  O tipo de estágio a ser editado.
     * @return \Illuminate\View\View
     */
    public function edit(InternshipType $internshipType)
    {
        $this->authorize('update', $internshipType);

        // Carrega todos os cursos para o dropdown de seleção.
        $courses = Course::all();

        return view('admin.internship_types.edit', compact('internshipType', 'courses'));
    }

    /**
     * Atualiza um tipo de estágio específico no banco de dados.
     *
     * @param  \App\Http\Requests\UpdateInternshipTypeRequest  $request  A requisição validada.
     * @param  \App\Models\InternshipType  $internshipType  O tipo de estágio a ser atualizado.
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(UpdateInternshipTypeRequest $request, InternshipType $internshipType)
    {
        // Atualiza o tipo de estágio com os dados validados.
        $internshipType->update($request->validated());

        // Redireciona para o formulário de edição com uma mensagem de sucesso.
        return redirect()->route('admin.internship-types.edit', $internshipType)
            ->with('message', 'Tipo de estágio atualizado com sucesso!')
            ->with('messageType', 'success');
    }

    /**
     * Remove o tipo de estágio especificado (soft delete).
     *
     * @param  \App\Models\InternshipType  $internshipType  O tipo de estágio a ser excluído.
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(InternshipType $internshipType)
    {
        try {
            $this->authorize('delete', $internshipType);
            $internshipType->delete();
        } catch (AuthorizationException $e) {
            return redirect()->route('admin.internship-types.index')->with('error', $e->getMessage());
        }

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
        $this->authorize('restore', $internshipType);
        // Restaura o registro.
        $internshipType->restore();

        // Redireciona para a lista de excluídos com uma mensagem de sucesso.
        return redirect()->route('admin.internship-types.index', ['show_deleted' => 1])
            ->with('message', 'Tipo de estágio restaurado com sucesso!')
            ->with('messageType', 'success');
    }
}
