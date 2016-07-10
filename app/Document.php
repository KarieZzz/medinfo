<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    // TODO: Может быть лучше перенести в конфиг?
    public static $state_labels = array(2 => 'Выполняется', 4 => 'Подготовлен к проверке', 8 => 'Принят', 16 => 'Возвращен на доработку', 32 => 'Утвержден' );
    public static $state_aliases = array(2 => 'performed', 4 => 'prepared', 8 => 'accepted', 16 => 'declined', 32 => 'approved' );
    public static $state_aliases_keys = array('performed' => 2, 'prepared' => 4, 'accepted' => 8, 'declined' => 16, 'approved' => 32);

    protected $fillable = [
        'ou_id' , 'period_id', 'form_id', 'state',
    ];
}
