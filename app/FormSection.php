<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class FormSection extends Model
{
    //
    protected $fillable = ['section_index', 'section_name'];

    public function form()
    {
        return $this->belongsTo('App\Form');
    }

    public function scopeOfForm($query, $form)
    {
        return $query
            //->orderBy('table_index')
            ->where('form_id', $form);
    }

}
