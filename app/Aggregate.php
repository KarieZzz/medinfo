<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Aggregate extends Model
{
    //
    protected $primaryKey = 'doc_id';
    protected $fillable = ['doc_id', 'protected', 'aggregated_at', 'include_docs', ];
    protected $dates = ['aggregated_at'];
}
