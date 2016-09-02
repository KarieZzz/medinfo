<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class FormGroup extends Model
{
    //
    protected $fillable = ['parent_id', 'group_name', ];

    public function forms()
    {
        return $this->hasMany('App\Forms', 'group_id', 'id');
    }
}
