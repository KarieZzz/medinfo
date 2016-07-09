<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DocumentAudition extends Model
{
    //
    public function dicauditstate()
    {
        return $this->hasOne('App\DicAuditState', 'code', 'state_id');
    }

    public function worker()
    {
        return $this->hasOne('App\Worker', 'id', 'user_id');
    }
}
