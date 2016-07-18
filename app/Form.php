<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Form extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'form_code', 'form_name', 'file_name', 'medstat_code', 'medinfo_id',
    ];

    public function tables()
    {
        return $this->hasMany('App\Table');
    }


}
