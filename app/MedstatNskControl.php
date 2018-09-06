<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MedstatNskControl extends Model
{
    //
    protected $fillable = ['form', 'table', 'error_type', 'left', 'right', 'relation', 'cycle', 'comment'];
    public $timestamps = false;

    public function form()
    {
        return $this->belongsTo('App\Form', 'table', 'medstatnsk_id');
    }

    public function scopeNSKForm($query, $nsk_form_id)
    {
        return $query->where('form', $nsk_form_id);
    }

    public function scopeNSKTableCode($query, $nsk_table_code)
    {
        return $query->where('table', $nsk_table_code);
    }

    public function scopeInterForm($query)
    {
        return $query->where('form', 0);
    }

    public function scopeInterTable($query)
    {
        return $query
            ->where('form', '<>' , 0)
            ->whereNull('table');
    }

    public function scopeInTable($query)
    {
        return $query
            ->where('form', '<>', 0)
            ->whereNotNull('table');
    }

}
