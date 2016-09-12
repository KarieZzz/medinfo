<?php

namespace App\Http\Controllers\StatDataInput;

class FormDashboardController extends DashboardController
{
    //
    public function __construct()
    {
        $this->middleware('datainputauth');
    }
}
