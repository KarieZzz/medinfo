<?php
/**
 * Created by PhpStorm.
 * User: shameev
 * Date: 28.06.2016
 * Time: 10:05
 */

namespace app\Medinfo;


class PeriodMM
{
    private $_year;
    private $_table;
    private static $periods = array(
        '2003' => '3',
        '2004' => 'a',
        '2005' => 'b',
        '2006' => 'c',
        '2007' => 'd',
        '2008' => 'e',
        '2009' => 'f',
        '2010' => 'g',
        '2011' => 'h',
        '2012' => 'i',
        '2013' => 'j',
        '2014' => 'k',
        '2015' => 'l',
        '2016' => 'm',
        '2017' => 'n',
        '2018' => 'o',
        '2019' => 'p',
        '2020' => 'q'
    );

    public function __construct($year = null)
    {
        if (!$year || !preg_match('/\d{4}/', $year)) {
            throw new Exception("Не указан отчетный год");
        }
        $this->_year  = $year;
        $this->_table = $this->set_table_name($year);
    }

    public static function getPeriodFromId($period_table)
    {
        $sign = substr($period_table, 6, 1);
        $period = new PeriodMM(array_search($sign, PeriodMM::$periods));
        return $period;
    }

    public static function getYearFromTable($period_table)
    {
        $sign = substr($period_table, 6, 1);
        return array_search($sign, PeriodMM::$periods);
    }

    private function set_table_name($year)
    {
        $base_tablename = 'l02345';
        $year_report_index = '0';
        if (array_key_exists($year, PeriodMM::$periods)) {
            return $base_tablename . PeriodMM::$periods[$year] . $year_report_index;
        }
        else {
            return null;
        }
    }

    public function getTableName()
    {
        return $this->_table;
    }

    public function getYear()
    {
        return $this->_year;
    }
}