<?php
/**
 * Created by PhpStorm.
 * User: shameev
 * Date: 01.10.2016
 * Time: 16:29
 */

namespace App\Medinfo;
use App\Table;

class TableEditing
{
    public static function fetchDataForTableRenedering(Table $table, $columntype = 'numberinput', $hiderowid = true)
    {
        if (!$table) {
            return [];
        }
        $fortable = [];
        $datafields_arr = array();
        $columns_arr = array();
        $datafields_arr[0] = array('name'  => 'id');
        $columns_arr[0] = array(
            'text'  => 'id',
            'dataField' => 'id',
            'width' => 50,
            'cellsalign' => 'left',
            'hidden' => $hiderowid,
            'pinned' => true
        );
        $column_groups_arr = array();
        $cols = $table->columns->where('deleted', 0)->sortBy('column_index');
        foreach ($cols as $col) {
            $datafields_arr[] = ['name'  => $col->id ];
            $width = $col->size * 10;
            switch ( $col->decimal_count) {
                case 2:
                    $editor = 'initDecimal2Editor';
                    break;
                case 3:
                    $editor = 'initDecimal3Editor';
                    break;
                default:
                    $editor = 'defaultEditor';
            }
            $contentType = $col->getMedinfoContentType();
            if ($contentType == 'data') {
                $columns_arr[] = array(
                    'text'  => $col->column_index,
                    'dataField' => $col->id,
                    'width' => $width,
                    'cellsalign' => 'right',
                    'align' => 'center',
                    //'cellsformat' => 'd' . $decimal_count,
                    'columntype' => $columntype,
                    //'columntype' => ,
                    'columngroup' => $col->id,
                    'filtertype' => 'number',
                    'cellclassname' => 'cellclass',
                    'cellbeginedit' => 'cellbeginedit',
                    'initeditor' => $editor,
                    'validation' => 'validation'
                );
                $column_groups_arr[] = array(
                    'text' => $col->column_name,
                    'align' => 'center',
                    'name' => $col->id,
                    'rendered' => 'tooltiprenderer'
                );
            } else if ($contentType == 'header') {
                $columns_arr[] = array(
                    'text' => $col->column_name,
                    'dataField' => $col->id,
                    'width' => $width,
                    'cellsalign' => 'left',
                    'align' => 'center',
                    'pinned' => true,
                    'editable' => false,
                    'filtertype' => 'textbox'
                );
            }
        }
        $fortable['tablecode'] = $table->table_code;
        $fortable['tablename'] = $table->table_name;
        $fortable['datafields'] = $datafields_arr;
        $fortable['columns'] = $columns_arr;
        $fortable['columngroups'] = $column_groups_arr;
        return $fortable;
    }
}