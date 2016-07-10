<?php

namespace App\Http\Controllers\StatDataInput;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\DocumentAudition;

class DocumentAuditionController extends Controller
{
    //
    public function fetchAuditions(Request $request)
    {
        $auditions = DocumentAudition::where('doc_id', $request->document)->with('dicauditstate', 'worker')->get();
        return $auditions;
    }
}
