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
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        // Garante que somente admins podem ver.
        $this->authorize('is-admin');

        $query = EmailLog::query()
            ->with('internship:id,student_name')
            ->leftJoin('internships', 'email_logs.internship_id', '=', 'internships.id')
            ->select('email_logs.*')
            ->orderBy('email_logs.created_at', 'desc');

        // Busca utilizando o SearchHelper nos campos da tabela local e relacionada
        $logs = SearchHelper::searchAndPaginate(
            $query,
            $request,
            $request->input('search'),
            ['email_logs.recipient', 'internships.student_name']
        );

        return view('admin.email-logs.index', compact('logs'));
    }
}
