<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InternshipAmendment;

class InternshipAmendmentController extends Controller
{
    public function destroy($id)
    {
        $amendment = InternshipAmendment::findOrFail($id);
        $amendment->delete();

        return back()->with('message', 'Aditivo removido com sucesso!')->with('messageType', 'success');
    }

    public function restore($id)
    {
        $amendment = InternshipAmendment::withTrashed()->findOrFail($id);
        $amendment->restore();

        return back()->with('message', 'Aditivo restaurado com sucesso!')->with('messageType', 'success');
    }
}
