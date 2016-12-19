<?php
/**
 * Created by PhpStorm.
 * User: shameev
 * Date: 05.08.2016
 * Time: 16:33
 */

namespace App\Medinfo;

use App\ControlCashe;
use Carbon\Carbon;

class ControlHelper
{

    public static function CashedProtocolActual(int $document, int $table)
    {
        //$updated_at =  self::dataUpdatedAt($document, $table);
        // Актуальность будем проверять по последней дате обновления всего документа по всем таблицам
        // Иначе неадекватно используется кэш
        $updated_at =  self::dataUpdatedAt($document);
        $cahed_at = self::protocolCashedAt($document, $table);

        return $cahed_at->gt($updated_at);
    }

    public static function chekoutCompareRule(array $condition)
    {
        $lp = $condition['left_part_value'];
        $rp = $condition['right_part_value'];
        $delta = 0.0001;
        // Если обе части выражения равны нулю - пропускаем проверку.
        if ($lp == 0 && $rp == 0) {
            return true;
        }
        switch ($condition['boolean_sign']) {
            case '==' :
                $result = abs($lp - $rp) < $delta ? true : false;
                break;
            case '>' :
                $result = $lp > $rp;
                break;
            case '>=' :
                $result = $lp >= $rp;
                break;
            case '<' :
                $result = $lp < $rp;
                break;
            case '<=' :
                $result = $lp <= $rp;
                break;
            case '^' :
                $result = ($lp && $rp) || (!$lp && !$rp);
                break;
            default:
                $result = false;
        }
        return $result;
    }

    public static function cashProtocol($protocol, int $document, int $table)
    {
        $protocol['cashed'] = true;
        $to_store = serialize($protocol);
        $ccashe = ControlCashe::firstOrCreate(['doc_id' => $document, 'table_id' => $table]);
        $ccashe->control_cashe = $to_store;
        $ccashe->cashed_at = Carbon::now();
        $ccashe->save();
        return true;
    }

    public static function loadProtocol(int $document, int $table)
    {
        $cache = unserialize(ControlCashe::OfDocumentTable($document, $table)->first(['control_cashe'])->control_cashe);
        return $cache ? $cache : false;
    }

    public static function protocolCashedAt(int $document, int $table)
    {
        $protocol = ControlCashe::OfDocumentTable($document, $table)->first(['cashed_at']);
        return $protocol ? $protocol->cashed_at : Carbon::create(1900, 1, 1);
    }

    public static function dataUpdatedAt(int $document)
    {
        if (!$document) {
            throw new \Exception("Не указан идентификатор документа для получения даты и времени сохранения данных");
        }
        //$q = "SELECT MAX(updated_at) latest_edited FROM statdata WHERE doc_id = {$document} AND table_id = {$table}";
        $q = "SELECT MAX(updated_at) latest_edited FROM statdata WHERE doc_id = {$document}";
        $updated_at = \DB::selectOne($q)->latest_edited;
        return $updated_at ? new Carbon($updated_at) : Carbon::create(1900, 1, 1);
    }

    public static function tableContainsData(int $document, int $table)
    {
        if (!$document || !$table) {
            throw new \Exception("Не указан идентификатор документа/таблицы для проверки наличия данных");
        }
        $q = "SELECT SUM(value) sum_of_values FROM statdata WHERE doc_id = $document AND table_id = $table";
        $res = \DB::selectOne($q);
        return $res->sum_of_values > 0 ? true : false;
    }
}