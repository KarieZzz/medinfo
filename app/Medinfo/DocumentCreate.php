<?php
/**
 * Created by PhpStorm.
 * User: shameev
 * Date: 03.10.2018
 * Time: 20:37
 */

namespace App\Medinfo;


use App\Monitoring;
use App\Period;
use App\UnitList;
use App\Unit;
use App\Document;

class DocumentCreate
{

    public static function documentBulkCreate($mode, array $units, Monitoring $monitoring, array $forms, int $album, Period $period, $initial_state, $create_primary = true, $create_aggregate = false )
    {
        $allowprimary = false;
        $allowaggregate = false;
        $i = 0;
        $duplicate = 0;
        foreach ($units as $unit_id) {
            if ($mode === '1') {
                $unit = Unit::find($unit_id);
                $unit->report ? $allowprimary = true : $allowprimary = false;
                $unit->aggregate ? $allowaggregate = true : $allowaggregate = false;
            }  elseif ($mode === '2') {
                $allowprimary = false;
                $allowaggregate = true;
                $unit = UnitList::find($unit_id);
                //dd($unit);
            }
            foreach ($forms as $form_id) {
                $newdoc = ['ou_id' => $unit->id, 'monitoring_id' => $monitoring->id, 'album_id' => $album, 'form_id' => $form_id ,
                    'period_id' => $period->id, 'state' => $initial_state ];

                if ($create_primary && $allowprimary) {
                    $newdoc['dtype'] = 1;
                    //Document::create($newdoc);
                    try {
                        Document::create($newdoc);
                        $i++;
                    } catch (\Illuminate\Database\QueryException $e) {
                        $errorCode = $e->errorInfo[0];
                        if($errorCode == '23505'){
                            $duplicate++;
                        }
                    }
                }
                if ($create_aggregate && $allowaggregate) {
                    $newdoc['dtype'] = 2;
                    //Document::create($newdoc);
                    try {
                        Document::create($newdoc);
                        $i++;
                    } catch (\Illuminate\Database\QueryException $e) {
                        $errorCode = $e->errorInfo[0];
                        if($errorCode == '23505'){
                            $duplicate++;
                        }
                    }
                }
            }
        }
        $data['count_of_created'] = $i;
        $data['count_of_duplicated'] = $duplicate;
        $data['count_of_all'] = count($units)*count($forms);
        return $data;
    }
}