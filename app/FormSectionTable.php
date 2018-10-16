<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class FormSectionTable extends Model
{
    //
    protected $fillable = [ 'formsection_id' , 'table_id'];

    public function formsection()
    {
        return $this->belongsTo('App\FormSection');
    }

    public function scopeOfFormSection($query, $section)
    {
        return $query
            ->where('formsection_id', $section);
    }
}
