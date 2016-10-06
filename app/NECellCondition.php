<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class NECellCondition extends Model
{
    //
    protected $table = 'necell_conditions';
    protected $fillable = ['condition_name', 'group_id', 'exclude'];

    public function group()
    {
        return $this->hasOne('App\UnitGroup', 'id', 'group_id');
    }
}
