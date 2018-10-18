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

    public function tables()
    {
        return $this->hasMany('App\FormSectionTable', 'formsection_id');
    }

    public function section_blocks()
    {
        return $this->hasMany('App\DocumentSectionBlock', 'formsection_id');
    }

    public function scopeOfForm($query, $form)
    {
        return $query
            ->where('form_id', $form);
    }



}
