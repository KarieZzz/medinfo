<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ConsUseRule extends Model
{
    //
    protected $fillable = ['row_id', 'col_id', 'script'];

    public function script()
    {
        return $this->belongsTo('App\ConsolidationRule', 'script');
    }

}
