<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ControlledColumn extends Model
{
    //
    protected $fillable = ['rec_id', 'controlled', 'controlling', 'boolean_sign', 'number_sign'];


}
