<?php

namespace App\Http\Controllers\StatDataInput;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    //
    /**
     * @param int $permission
     * @param int $document_state
     * @return bool
     */
    protected function isEditPermission(int $permission, int $document_state)
    {
        switch (true) {
            case (($permission & config('app.permission.permission_edit_report')) && ($document_state == 2 || $document_state == 16)) :
                $edit_permission = true;
                break;
            case (($permission & config('app.permission.permission_edit_prepared_report')) && $document_state == 4) :
                $edit_permission = true;
                break;
            case (($permission & config('app.permission.permission_edit_accepted_report')) && $document_state == 8) :
                $edit_permission = true;
                break;
            case (($permission & config('app.permission.permission_edit_approved_report')) && $document_state == 32) :
                $edit_permission = true;
                break;
            case (($permission & config('app.permission.permission_edit_aggregated_report')) && $document_state == 0) :
                $edit_permission = true;
                break;
            default:

                $edit_permission = false;
        }
        return $edit_permission;
    }

    /**
     * @param int $document
     * @return array
     */
    protected function getEditedTables(int $document)
    {
        $editedtables = \DB::table('statdata')
            ->join('documents', 'documents.id' ,'=', 'statdata.doc_id')
            ->leftJoin('tables', 'tables.id', '=', 'statdata.table_id')
            ->where('documents.id', $document)
            ->where('tables.deleted', 0)
            ->groupBy('statdata.table_id')
            ->pluck('statdata.table_id');
        return $editedtables;
    }
}
