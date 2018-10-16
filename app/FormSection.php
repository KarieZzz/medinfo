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

    public function albums()
    {
        return $this->hasMany('App\AlbumFormsection', 'formsection_id');
    }

    public function scopeOfForm($query, $form)
    {
        return $query
            ->where('form_id', $form);
    }



}
