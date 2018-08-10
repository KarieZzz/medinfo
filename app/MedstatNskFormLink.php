<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MedstatNskFormLink extends Model
{
    //
    protected $fillable = ['form_name', 'decipher', 'ind', 'medstat_code'];

    public function scopeOfCode($query, $code)
    {
        return $query
            ->where('form_name', $code);
    }

}
