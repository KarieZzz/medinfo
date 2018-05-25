<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

class ConsRulesAndListsAdminController extends Controller
{
    //
    public function __construct()
    {
        $this->middleware('admins');
    }

    public function index()
    {
        $forms = \App\Form::orderBy('form_index')->get(['id', 'form_code']);
        return view('jqxadmin.set_consrules_and_lists', compact('forms'));
    }

    public function applyRule(Request $request)
    {

    }

    public function applyList(Request $request)
    {
        $this->validate($request, $this->validateListRequest());
        $coordinates = explode(',', $request->cells);
        $hashed  =  sprintf("%u", crc32(preg_replace('/\s+/u', '', $request->list)));
        //dd($hashed);
        $list = \App\ConsolidationList::firstOrNew(['hash' => $hashed]);
        $list->script = $request->list;
        $list->save();
        $i = 0;
        foreach ($coordinates as $coordinate) {
            list($row, $column) = explode('_', $coordinate);
            $apply_list = \App\ConsUseList::firstOrNew(['row_id' => $row, 'col_id' => $column]);
            $apply_list->list = $list->id;
            $apply_list->save();
            $i++;
        }
        return ['affected_cells' => $i ];
    }

    protected function validateListRequest()
    {
        return [
            'list' => 'required|min:1|max:512',
            'comment' => 'max:128',
            'cells' => 'required',
        ];
    }

}
