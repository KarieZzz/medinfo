<?php

namespace App\Http\Controllers\Tests;

use Ds\Vector;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

class VectorTestController extends Controller
{
    //
    public function index()
    {
        $forms = \App\Form::all()->sortBy('form_index')->pluck('form_index');
        $vector = new Vector($forms);
        //$vector->map(function($value) { $value->form_index = } );
        //$vector->insert(1, "f");
        /*foreach ($forms as $form) {
            $correct_index = $vector->find($form);
            dump($correct_index);
            $f = \App\Form::where('form_index', $form)->first();
            $f->form_index = $correct_index;
            $f->save();
        }*/

        dd($vector);
        dd($vector->find(11));
        //dd(\App\Form::all()->sortBy('form_index')->pluck('form_index'));
    }
}
