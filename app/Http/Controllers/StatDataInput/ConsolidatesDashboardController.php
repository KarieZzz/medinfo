<?php

namespace App\Http\Controllers\StatDataInput;

use App\Consolidate;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

class ConsolidatesDashboardController extends DashboardController
{
    //
    protected $dashboardView = 'jqxdatainput.consolidatedashboard';

    public function __construct()
    {
        $this->middleware('datainputauth');
    }

    public function fetchConsolidationProtocol(\App\Document $document, \App\Row $row, \App\Column $column)
    {
        $consolidate = Consolidate::OfRowColumn($row->id, $column->id)->first();
        if ( isset($consolidate->protocol) ) {
            return $consolidate->protocol;
        }
        return null;
    }
}
