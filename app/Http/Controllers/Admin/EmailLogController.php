<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmailLog;
use App\Utils\SearchHelper;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

class EmailLogController extends Controller
{
    use AuthorizesRequests;

    /**
     * Exibe a listagem dos logs de e-mail.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $this->authorize('is-admin');

        $query = EmailLog::query()
            ->with('internship:id,student_name')
            ->orderBy('created_at', 'desc');

        // Busca pelo nome do aluno usando SearchHelper (mesmo padrão do InternshipController)
        $search = $request->input('search');

        if (filled($search)) {
            $internshipIds = \App\Models\Internship::query()
                ->tap(function ($q) use ($search) {
                    SearchHelper::applyUnaccentSearchIfSupported($q, $search, 'student_name');
                })
                ->pluck('id');

            $query->whereIn('internship_id', $internshipIds);
        }

        $logs = $query->paginate(100);

        return view('admin.email-logs.index', compact('logs'));
    }
}
