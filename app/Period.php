<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Period extends Model
{
    //
    protected $fillable = ['name', 'begin_date', 'end_date', 'pattern_id', 'medinfo_id'];
    protected $dates = ['begin_date', 'end_date',];

    public function scopeLastYear($query, int $year)
    {
        $date = ($year - 1 ) . '-01-01';
        return $query

            ->where('begin_date', $date)
            ->where('pattern_id', 1); // 1 - Паттерн годового отчетного периода
    }

}
