<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Period extends Model
{
    //
    public static $period_cycles = [
        1 => 1, // годовые
        2 => 5, 3 => 2, 4 => 3, 5 => 4, // квартальные
        6 => 9, 7 => 6, 8 => 7, 9 => 8, // квартальные накопительные
        10 => 11, 11 => 10, // полугодовые
    ];
    protected $fillable = ['name', 'begin_date', 'end_date', 'pattern_id', 'medinfo_id'];
    protected $dates = ['begin_date', 'end_date',];

    public function scopeLastYear($query)
    {
        $date = ((int)date("Y") - 1 ) . '-01-01';
        return $query

            ->where('begin_date', $date)
            ->where('pattern_id', 1); // 1 - Паттерн годового отчетного периода
    }

    public function scopePreviousYear($query, $current_year)
    {
        $date = ((int)$current_year - 1 ) . '-01-01';
        return $query

            ->where('begin_date', $date)
            ->where('pattern_id', 1); // 1 - Паттерн годового отчетного периода
    }

    public function scopePreviousQuarter($query, $current_qurter)
    {

    }

    public function periodpattern()
    {
        return $this->belongsTo('App\PeriodPattern', 'pattern_id');
    }
}

