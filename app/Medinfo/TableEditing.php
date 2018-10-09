<?php
/**
 * Created by PhpStorm.
 * User: shameev
 * Date: 01.10.2016
 * Time: 16:29
 */

namespace App\Medinfo;

use App\Album;
use App\Column;
use App\Table;

class TableEditing
{
    public static function fetchDataForTableRenedering(Table $table, Album $album , $columntype = 'numberinput', $hiderowid = true)
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
        $calculated_fields = array();
        $column_groups_arr = array();
        //$cols = $table->columns->where('deleted', 0)->sortBy('column_index');

        $cols = Column::OfTable($table->id)->orderBy('column_index')->whereDoesntHave('excluded', function ($query) use($album) {
            $query->where('album_id', $album->id);
        })->get();
        $firstDataColumn = null;
        foreach ($cols as $col) {
            $datafields_arr[] = ['name'  => $col->id, 'type'  => 'string', ];
            $width = $col->size; // Ширина графы в пикселях при отображении в браузере
            switch ( $col->decimal_count) {
                case 1:
                    $editor = 'initDecimal1Editor';
                    break;
                case 2:
                    $editor = 'initDecimal2Editor';
                    break;
                case 3:
                    $editor = 'initDecimal3Editor';
                    break;
                default:
                    $editor = 'defaultEditor';
            }
            if ($col->content_type === Column::DATA) {
                if (!$firstDataColumn) {
                    $firstDataColumn = $col->id;
                }
                $columns_arr[] = array(
                    'text'  => $col->column_code,
                    'dataField' => $col->id,
                    'width' => $width,
                    'cellsalign' => 'right',
                    'align' => 'center',
                    'cellsrenderer' => 'cellsrenderer',
                    //'cellsformat' => 'n',
                    //'cellsformat' => 'd' . $decimal_count,
                    'columntype' => $columntype,
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
            } elseif ($col->content_type === Column::CALCULATED) {
                $calculated_fields[] = $col->id;
                $columns_arr[] = array(
                    'text' => $col->column_index,
                    'dataField' => $col->id,
                    'width' => $width,
                    'cellsalign' => 'right',
                    'align' => 'center',
                    'cellsrenderer' => 'cellsrenderer',
                    'columntype' => $columntype,
                    'columngroup' => $col->id,
                    'pinned' => false,
                    'editable' => false,
                    'filtertype' => 'number',
                    'cellclassname' => 'calculated'
                );
                $column_groups_arr[] = array(
                    'text' => $col->column_name,
                    'align' => 'center',
                    'name' => $col->id,
                    'rendered' => 'tooltiprenderer'
                );
            } elseif ($col->content_type === Column::HEADER) {
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
        $fortable['index'] = $table->table_index;
        $fortable['firstdatacolumn'] = $firstDataColumn;
        $fortable['datafields'] = $datafields_arr;
        $fortable['calcfields'] = $calculated_fields;
        $fortable['columns'] = $columns_arr;
        $fortable['columngroups'] = $column_groups_arr;
        //if ($table->id === 31) {
          //  dd($columns_arr);
        //}

        return $fortable;
    }

    public static function tableRender(Table $table, $columntype = 'textbox')
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
            'pinned' => true
        );
        $column_groups_arr = array();
        $cols = Column::OfTable($table->id)->orderBy('column_index')->get();
        foreach ($cols as $col) {
            $datafields_arr[] = ['name'  => $col->id, 'type'  => 'string', ];
            $width = $col->size;
            $contentType = $col->getMedinfoContentType();
            if ($contentType == 'data') {
                $columns_arr[] = array(
                    'text'  => $col->column_index,
                    'dataField' => $col->id,
                    'width' => $width,
                    //'cellsalign' => 'right',
                    'align' => 'center',
                    'columntype' => $columntype,
                    'columngroup' => $col->id,
/*                    'filtertype' => 'number',
                    'cellclassname' => 'cellclass',
                    'cellbeginedit' => 'cellbeginedit',
                    'validation' => 'validation'*/
                );
                $column_groups_arr[] = array(
                    'text' => $col->column_name,
                    'align' => 'center',
                    'name' => $col->id,
                    //'rendered' => 'tooltiprenderer'
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
                    //'filtertype' => 'textbox'
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