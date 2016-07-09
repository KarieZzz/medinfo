<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    //
    protected $table = 'mo_hierarchy';
    protected $fillable = [
        'parent_id', 'unit_code', 'inn', 'node_type', 'report', 'aggregate', 'unit_name', 'blocked',
    ];
}
