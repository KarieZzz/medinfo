<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MedstatNskData extends Model
{
    //
    protected $table = 'medstat_nsk_data';
    protected $fillable = ['hospital', 'data', 'year', 'table', 'column', 'row'];
    public $timestamps = false;

    public function tablensk()
    {
        return $this->belongsTo('App\MedstatNskTableLink', 'table', 'id');
    }
    public function table()
    {
        return $this->belongsTo('App\Table', 'table', 'medstatnsk_id');
    }

}
