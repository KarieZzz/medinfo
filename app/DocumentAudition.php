<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DocumentAudition extends Model
{
    //
    public static $audit_labels = array(1 => 'Не проверен', 2 => 'Проверен, замечаний нет', 3 => 'Проверен, имеются замечания');
    public static $audit_aliases = array(1 => 'noaudit', 2 => 'audit_correct', 3 => 'audit_incorrect');
    public static $audit_alias_keys = array('noaudit' => 1, 'audit_correct' => 2, 'audit_incorrect' => 3, 'nobatchaudit' => 1, 'batch_audit_correct' => 2, 'batch_audit_incorrect' => 3, );

    public function dicauditstate()
    {
        return $this->hasOne('App\DicAuditState', 'code', 'state_id');
    }

    public function worker()
    {
        return $this->hasOne('App\Worker', 'id', 'user_id');
    }

    public function scopeByWorkerAndDocument($query, $worker, $document)
    {
        return $query->where('user_id', $worker)->where('doc_id', $document);
    }
}
