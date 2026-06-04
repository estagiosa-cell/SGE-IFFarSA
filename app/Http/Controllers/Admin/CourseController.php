<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCourseRequest;
use App\Http\Requests\UpdateCourseRequest;
use App\Models\Course;
use App\Models\User;
use App\Utils\SearchHelper;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

/**
 * Controlador para gerenciar os Cursos no painel administrativo.
 *
 * Este controlador lida com a listagem, criação, edição, exclusão,
 * e restauração de cursos do sistema.
 */
class CourseController extends Controller
{
    use AuthorizesRequests;

    /**
     * Exibe uma listagem dos cursos.
     *
     * @param  \Illuminate\Http\Request  $request  A requisição HTTP.
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Course::class);

        // Inicia a query com o carregamento antecipado do coordenador para otimização.
        $query = Course::with('coordinator:id,name');

        // Verifica se o filtro 'show_deleted' está ativo para incluir cursos removidos (soft delete).
        $showDeleted = $request->input('show_deleted') === '1';
        if ($showDeleted) {
            $query = $query->onlyTrashed();
        }

        $orderedQuery = $query->orderBy('name');
        $courses = SearchHelper::searchAndPaginate(
            $orderedQuery,
            $request,
            $request->input('search'),
            'name'
        );

        $activeFiltersCount = collect([
            $request->input('search'),
        ])->filter(fn ($v) => filled($v))->count();

        // Retorna a view com a lista de cursos e o estado do filtro de excluídos.
        return view('admin.courses.index', compact('courses', 'showDeleted', 'activeFiltersCount'));
    }

    /**
     * Exibe o formulário para criação de um novo curso.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $this->authorize('create', Course::class);

        // Busca todos os usuários que são coordenadores para preencher o select no formulário.
        $coordinators = User::coordinators();

        return view('admin.courses.create', compact('coordinators'));
    }

    /**
     * Armazena um novo curso no banco de dados.
     *
     * @param  \App\Http\Requests\StoreCourseRequest  $request  A requisição validada.
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(StoreCourseRequest $request)
    {
        // Cria o curso com os dados validados pela StoreCourseRequest.
        Course::create($request->validated());

        return redirect()
            ->route('admin.courses.index')
            ->with('message', 'Curso criado com sucesso!')
            ->with('messageType', 'success');
    }

    /**
     * Exibe o formulário para edição do curso especificado.
     *
     * @param  \App\Models\Course  $course  O curso a ser editado.
     * @return \Illuminate\View\View
     */
    public function edit(Course $course)
    {
        $this->authorize('update', $course);

        // Busca todos os usuários que são coordenadores para o formulário.
        $coordinators = User::coordinators();

        return view('admin.courses.edit', compact('course', 'coordinators'));
    }

    /**
     * Atualiza o curso especificado no banco de dados.
     *
     * @param  \App\Http\Requests\UpdateCourseRequest  $request  A requisição validada.
     * @param  \App\Models\Course  $course  O curso a ser atualizado.
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(UpdateCourseRequest $request, Course $course)
    {
        // Atualiza o curso com os dados validados.
        $course->update($request->validated());

        return redirect()
            ->route('admin.courses.edit', $course)
            ->with('message', 'Curso atualizado com sucesso!')
            ->with('messageType', 'success');
    }

    /**
     * Remove o curso especificado do sistema (soft delete).
     *
     * @param  \App\Models\Course  $course  O curso a ser excluído.
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Course $course)
    {
        try {
            $this->authorize('delete', $course);
            $course->delete();
        } catch (AuthorizationException $e) {
            return redirect()->route('admin.courses.index')->with('error', $e->getMessage());
        }

        return redirect()->route('admin.courses.index')
            ->with('message', 'Curso excluído com sucesso!')
            ->with('messageType', 'success');
    }

    /**
     * Restaura um curso que foi removido via soft delete.
     *
     * @param  string  $id  O ID do curso a ser restaurado.
     * @return \Illuminate\Http\RedirectResponse
     */
    public function restore($id)
    {
        // Busca o curso apenas na lixeira (onlyTrashed).
        $course = Course::onlyTrashed()->findOrFail($id);
        $this->authorize('restore', $course);
        $course->restore();

        // Redireciona de volta para a lista de cursos excluídos.
        return redirect()->route('admin.courses.index', ['show_deleted' => 1])
            ->with('message', 'Curso restaurado com sucesso!')
            ->with('messageType', 'success');
    }
}
