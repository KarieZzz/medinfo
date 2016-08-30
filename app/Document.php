<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Document extends Model
{
    public static $dtype_labels = [1 => 'Первичный', 2 => 'Сводный'];
    public static $state_labels = [
        2 => 'Выполняется', 4 => 'Подготовлен к проверке', 8 => 'Принят', 16 => 'Возвращен на доработку', 32 => 'Утвержден'
    ];
    public static $state_aliases = [
        2 => 'performed', 4 => 'prepared', 8 => 'accepted', 16 => 'declined', 32 => 'approved'
    ];
    public static $state_aliases_keys = [
        'performed' => 2, 'prepared' => 4, 'accepted' => 8, 'declined' => 16, 'approved' => 32
    ];

    protected $fillable = [
        'dtype', 'ou_id' , 'period_id', 'form_id', 'state',
    ];
    protected $dates = ['state_changed_at'];

    public function scopePrimary($query)
    {
        return $query->where('dtype', 1);
    }

    public function scopeAggregate($query)
    {
        return $query->where('dtype', 2);
    }
    public function scopeOfUPF($query, $ou, $period, $form)
    {
        return $query
            ->where('ou_id', $ou)
            ->where('period_id', $period)
            ->where('form_id', $form);
    }

    public static function dataUpdatedAt(int $document)
    {
        $q = "SELECT MAX(updated_at) latest_edited FROM statdata WHERE doc_id = $document";
        $updated_at = \DB::selectOne($q)->latest_edited;
        if ($updated_at) {
            return new Carbon($updated_at);
        } else {
            // Возвращаем объект с заведомо старой датой
            return Carbon::create(1900, 1, 1);
        }
    }
}
