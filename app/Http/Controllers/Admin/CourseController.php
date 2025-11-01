<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\CourseRequest;
use App\Models\Course;
use App\Models\User;
use App\Utils\SearchHelper;
use Illuminate\Http\Request;

/**
 * Controlador para gerenciar os Cursos no painel administrativo.
 *
 * Este controlador lida com a listagem, criação, edição, exclusão,
 * e restauração de cursos do sistema.
 */
class CourseController extends Controller
{
    /**
     * Exibe uma listagem dos cursos.
     *
     * @param  \Illuminate\Http\Request  $request  A requisição HTTP.
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        // Inicia a query com o carregamento antecipado do coordenador para otimização.
        $query = Course::with('coordinator');

        // Verifica se o filtro 'show_deleted' está ativo para incluir cursos removidos (soft delete).
        $showDeleted = $request->input('show_deleted') === '1';
        if ($showDeleted) {
            $query = $query->onlyTrashed();
        }

        // Aplica o filtro de busca por nome, se presente na requisição.
        if ($request->filled('search')) {
            SearchHelper::searchInField($query, $request->search, 'name');
        }

        // Ordena os cursos por nome e pagina os resultados.
        $courses = $query->orderBy('name')->paginate(100);

        // Retorna a view com a lista de cursos e o estado do filtro de excluídos.
        return view('admin.courses.index', compact('courses', 'showDeleted'));
    }

    /**
     * Exibe o formulário para criação de um novo curso.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        // Busca todos os usuários que são coordenadores para preencher o select no formulário.
        $coordinators = User::coordinators();

        return view('admin.courses.create', compact('coordinators'));
    }

    /**
     * Armazena um novo curso no banco de dados.
     *
     * @param  \App\Http\Requests\CourseRequest  $request  A requisição validada.
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(CourseRequest $request)
    {
        // Cria o curso com os dados validados pela CourseRequest.
        Course::create($request->validated());

        return redirect()
            ->route('admin.courses.index')
            ->with('message', 'Curso criado com sucesso!')
            ->with('messageType', 'success');
    }

    /**
     * Exibe o formulário para edição do curso especificado.
     *
     * @param  string  $id  O ID do curso.
     * @return \Illuminate\View\View
     */
    public function edit(string $id)
    {
        // Encontra o curso pelo ID ou falha.
        $course = Course::findOrFail($id);
        // Busca todos os usuários que são coordenadores para o formulário.
        $coordinators = User::coordinators();

        return view('admin.courses.edit', compact('course', 'coordinators'));
    }

    /**
     * Atualiza o curso especificado no banco de dados.
     *
     * @param  \App\Http\Requests\CourseRequest  $request  A requisição validada.
     * @param  string  $id  O ID do curso.
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(CourseRequest $request, string $id)
    {
        // Encontra o curso pelo ID ou falha.
        $course = Course::findOrFail($id);
        // Atualiza o curso com os dados validados.
        $course->update($request->validated());

        return redirect()
            ->route('admin.courses.edit', $id)
            ->with('message', 'Curso atualizado com sucesso!')
            ->with('messageType', 'success');
    }

    /**
     * Remove o curso especificado do sistema (soft delete).
     *
     * @param  string  $id  O ID do curso a ser excluído.
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(string $id)
    {
        $course = Course::findOrFail($id);
        $course->delete();

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
        $course->restore();

        // Redireciona de volta para a lista de cursos excluídos.
        return redirect()->route('admin.courses.index', ['show_deleted' => 1])
            ->with('message', 'Curso restaurado com sucesso!')
            ->with('messageType', 'success');
    }
}
