<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Album;
use App\Form;
use App\Table;
use App\Row;
use App\AlbumRowSet;
use App\Column;
use App\AlbumColumnSet;
use App\Cell;
use App\DicColumnType;

class RowColumnAdminController extends Controller
{
    //
    public function __construct()
    {
        $this->middleware('admins');
    }

    public function index()
    {
        $forms = Form::orderBy('form_code')->get(['id', 'form_code', 'form_name']);
        $columnTypes = DicColumnType::all();
        $album = $this->getDefaultAlbum();
        return view('jqxadmin.rowcolumns', compact('forms', 'columnTypes', 'album'));
    }

    public function getDefaultAlbum()
    {
        $default_album = Album::Default()->first();
        if (is_null($default_album)) {
            $default_album = Album::find(config('medinfo.default_album'));
        }
        return $default_album;
    }

    public function fetchTables(int $form)
    {
        return Table::OfForm($form)->orderBy('table_index')->with('form')->get();
    }

    public function fetchRows(int $table)
    {
        $album = $this->getDefaultAlbum();
        return Row::OfTable($table)->with('table')->with(['excluded' => function ($query) use ($album) {
            $query->where('album_id', $album->id);
        }])->orderBy('row_index')->get();
    }

    public function fetchColumns(int $table)
    {
        $album = $this->getDefaultAlbum();
        return Column::OfTable($table)->orderBy('column_index')->with('table')->with(['excluded' => function ($query) use ($album) {
            $query->where('album_id', $album->id);
        }])->get();
    }

    public function rowUpdate(Row $row, Request $request)
    {
        $this->validate($request, [
                'row_index' => 'required|integer|min:1|max:999',
                'row_name' => 'required|max:256',
                'row_code' => 'required|max:16',
                'medstat_code' => 'digits:3',
                'medstatnsk_id' => 'integer',
                'excluded' => 'required|in:1,0',
            ]
        );
        $row->row_index = $request->row_index;
        $row->row_code = $request->row_code;
        $row->row_name = $request->row_name;
        $row->medstat_code = empty($request->medstat_code) ? null : $request->medstat_code;
        $row->medstatnsk_id = empty($request->medstatnsk_id) ? null : $request->medstatnsk_id;
        $result = [];
        try {
            $row->save();
            $exclude = AlbumRowSet::setRow($request->excluded, $row->id);
            $add = '';
            if ($exclude === 1) {
                $add = "Строка удалена из списка исключенных в текущем альбоме форм";
            }
            $result = ['message' => 'Запись id ' . $row->id . ' сохранена. ' . $add];
        } catch (\Illuminate\Database\QueryException $e) {
            $errorCode = $e->errorInfo[0];
            if($errorCode == '23505'){
                $result = ['error' => 422, 'message' => 'Запись не сохранена. Дублирование данных.'];
            }
        }
        return $result;
    }

    public function rowStore(Request $request)
    {
        $this->validate($request, [
                'table_id' => 'required|exists:tables,id',
                'row_index' => 'required|integer|min:1|max:999',
                'row_name' => 'required|max:256',
                'row_code' => 'required|max:16',
                'medstat_code' => 'digits:3',
                'medstatnsk_id' => 'integer',
                'excluded' => 'required|in:1,0',
            ]
        );
        $code_exists = Row::OfTableRowCode($request->table_id, $request->row_code)->exists();
        if ($code_exists) {
            return ['error' => 422, 'message' => 'Запись с таким же кодом строки уже существует в этой таблице'];
        }
        $index_exists = Row::OfTableRowIndex($request->table_id, $request->row_index)->exists();
        if ($index_exists) {
            $reindexed = Row::OfTable($request->table_id)->where('row_index','>=', $request->row_index)->orderBy('row_index', 'desc')->get();
            foreach ($reindexed as $item) {
                $item->row_index++;
                $item->save();
            }
        }
        $newrow = new Row;
        $newrow->table_id = $request->table_id;
        $newrow->row_index = $request->row_index;
        $newrow->row_code = $request->row_code;
        $newrow->row_name = $request->row_name;
        $newrow->medstat_code = empty($request->medstat_code) ? null : $request->medstat_code;
        $newrow->medstatnsk_id = empty($request->medstatnsk_id) ? null : $request->medstatnsk_id;
        try {
            $newrow->save();
            return ['message' => 'Новая запись создана. Id:' . $newrow->id, 'id' => $newrow->id];
        } catch (\Illuminate\Database\QueryException $e) {
            $errorCode = $e->errorInfo[0];
            switch ($errorCode) {
                case '23505':
                    $message = 'Запись не сохранена. Дублирующиеся значения.';
                    break;
                default:
                    $message = 'Новая запись не создана. Код ошибки ' . $errorCode . '.';
                    break;
            }
            return ['error' => 422, 'message' => $message];
        }
    }

    public function rowDelete(Row $row)
    {
        if (!$row) {
            return ['error' => 422, 'message' => 'Ошибка удаления. Строка не найдена'];
        }
        $cell_count = Cell::countOfCellsByRow($row->id);
        $table_id = $row->table_id;
        $row_index = $row->row_index;
        if ($cell_count == 0) {
            $row->delete();
            $reindexed = Row::OfTable($table_id)->where('row_index','>', $row_index)->orderBy('row_index')->get();
            foreach ($reindexed as $item) {
                $item->row_index--;
                $item->save();
            }
            return ['message' => 'Удалена строка Id' . $row->id ];
        } else {
            return ['error' => 422, 'message' => 'Строка Id' . $row->id . ' содержит данные. Удаление невозможно.' ];
        }
    }

    public function columnUpdate(Column $column, Request $request)
    {
        $this->validate($request, [
                'column_index' => 'required|integer|min:1|max:50',
                'column_name' => 'required|max:256',
                'column_code' => 'required|max:8',
                'content_type' => 'integer',
                'field_size' => 'integer',
                'decimal_count' => 'integer',
                'medstat_code' => 'digits:2',
                'medstatnsk_id' => 'integer',
                'excluded' => 'required|in:1,0',
            ]
        );
        $column->column_index = $request->column_index;
        $column->column_name = $request->column_name;
        $column->column_code = $request->column_code;
        $column->content_type = $request->content_type;
        $column->size = $request->field_size ? (int)$request->field_size : 100;
        $column->decimal_count = $request->decimal_count;
        $column->medstat_code = empty($request->medstat_code) ? null : $request->medstat_code;
        $column->medstatnsk_id = empty($request->medstatnsk_id) ? null : $request->medstatnsk_id;
        $result = [];
        try {
            $column->save();
            $exclude = AlbumColumnSet::setColumn($request->excluded, $column->id);
            $add = '';
            if ($exclude === 1) {
                $add = "Графа удалена из списка исключенных в текущем альбоме форм";
            }
            $result = ['message' => 'Запись id ' . $column->id . ' сохранена ' . $add];
        } catch (\Illuminate\Database\QueryException $e) {
            $errorCode = $e->errorInfo[0];
            if($errorCode == '23505'){
                $result = ['error' => 422, 'message' => 'Запись не сохранена. Дублирование данных.'];
            }
        }
        return $result;
    }

    public function columnStore(Request $request)
    {
        $this->validate($request, [
                'table_id' => 'required|exists:tables,id',
                'column_index' => 'required|integer|min:1|max:50',
                'column_name' => 'required|max:256',
                'column_code' => 'required|max:8',
                'content_type' => 'integer',
                'field_size' => 'integer',
                'decimal_count' => 'integer',
                'medstat_code' => 'digits:2',
                'medstatnsk_id' => 'integer',
                'excluded' => 'required|in:1,0',
            ]
        );
        $code_exists = Column::OfTableColumnCode($request->table_id, $request->column_code)->exists();
        if ($code_exists) {
            return ['error' => 422, 'message' => 'Запись с таким же кодом графы уже существует в этой таблице'];
        }
        $index_exists = Column::OfTableColumnIndex($request->table_id, $request->column_index)->exists();
        if ($index_exists) {
            $reindexed = Column::OfTable($request->table_id)->where('column_index','>=', $request->column_index)->orderBy('column_index', 'desc')->get();
            foreach ($reindexed as $item) {
                $item->column_index++;
                $item->save();
            }
        }
        $newcolumn = new Column;
        $newcolumn->table_id = $request->table_id;
        $newcolumn->column_index = $request->column_index;
        $newcolumn->column_name = $request->column_name;
        $newcolumn->column_code = $request->column_code;
        $newcolumn->content_type = $request->content_type;
        $newcolumn->size = $request->field_size ? (int)$request->field_size : 100;
        $newcolumn->decimal_count = empty($request->decimal_count) ? 0 : $request->decimal_count;
        $newcolumn->medstat_code = empty($request->medstat_code) ? null : $request->medstat_code;
        $newcolumn->medstatnsk_id = empty($request->medstatnsk_id) ? null : $request->medstatnsk_id;
        try {
            $newcolumn->save();
            return ['message' => 'Новая запись создана. Id:' . $newcolumn->id, 'id' => $newcolumn->id];
        } catch (\Illuminate\Database\QueryException $e) {
            $errorCode = $e->errorInfo[0];
            switch ($errorCode) {
                case '23505':
                    $message = 'Запись не сохранена. Дублирующиеся значения (' . $errorCode . ').';
                    break;
                default:
                    $message = 'Новая запись не создана. Код ошибки ' . $errorCode . '.';
                    break;
            }
            return ['error' => 422, 'message' => $message];
        }
    }

    public function columnDelete(Column $column)
    {
        if (!$column) {
            return ['error' => 422, 'message' => 'Ошибка удаления. Графа не найдена'];
        }
        $cell_count = Cell::countOfCellsByColumn($column->id);
        $table_id = $column->table_id;
        $column_index = $column->row_index;
        if ($cell_count === 0) {
            $column->delete();
            $reindexed = Column::OfTable($table_id)->where('row_index','>', $column_index)->orderBy('column_index')->get();
            foreach ($reindexed as $item) {
                $item->column_index--;
                $item->save();
            }
            return ['message' => 'Удалена графа Id' . $column->id ];
        } else {
            return ['error' => 422, 'message' => 'Графа Id' . $column->id . ' содержит данные. Удаление невозможно.' ];
        }
    }

    public function rowUp(Row $row)
    {
        $current_index = $row->row_index;
        //dd($current_index-1);
        $prevrow = Row::OfTable($row->table_id)->where('row_index', $current_index - 1)->first();
        //dd($prevtable);
        if(is_null($prevrow)){
            return ['error' => 422, 'message' => 'Выше некуда.'];
        }
        $row->row_index = 0;
        $row->save();
        $prevrow->row_index = $current_index;
        $prevrow->save();
        $row->row_index = $current_index - 1;
        $row->save();
        return [$current_index, $current_index - 1];
    }

    public function rowDown(Row $row)
    {
        $current_index = $row->row_index;
        //dd($current_index-1);
        $nextrow = Row::OfTable($row->table_id)->where('row_index', $current_index + 1)->first();
        //dd($prevtable);
        if(is_null($nextrow)){
            return ['error' => 422, 'message' => 'Ниже некуда.'];
        }
        $row->row_index = 0;
        $row->save();
        $nextrow->row_index = $current_index;
        $nextrow->save();
        $row->row_index = $current_index + 1;
        $row->save();
        return [$current_index, $current_index + 1];
    }

    public function columnLeft(Column $column)
    {
        $current_index = $column->column_index;
        //dd($current_index-1);
        $prevcol = Column::OfTable($column->table_id)->where('column_index', $current_index - 1)->first();
        //dd($prevcol);
        if(is_null($prevcol)){
            return ['error' => 422, 'message' => 'Левее некуда.'];
        }
        $column->column_index = 0;
        $column->save();
        $prevcol->column_index = $current_index;
        $prevcol->save();
        $column->column_index = $current_index - 1;
        $column->save();
        return [$current_index, $current_index - 1];
    }

    public function columnRight(Column $column)
    {
        $current_index = $column->column_index;
        $nextcol = Column::OfTable($column->table_id)->where('column_index', $current_index + 1)->first();
        if(is_null($nextcol)){
            return ['error' => 422, 'message' => 'Правее некуда.'];
        }
        $column->column_index = 0;
        $column->save();
        $nextcol->column_index = $current_index;
        $nextcol->save();
        $column->column_index = $current_index + 1;
        $column->save();
        return [$current_index, $current_index + 1];
    }

    // Сопоставление строк в Мединфо и в Медстат
    public function rowsMatching($formcode)
    {
        $matching_array = [];
        $errors = [];
        $mode = 'строки';
        $default_album = Album::Default()->first(['id']);
        //$form = Form::find(74);
        $form = Form::OfCode($formcode)->first();
        //$form = Form::find(7);
        // обрабатываем таблицы из Мединфо не исключенные из текущего альбома и имеющие код Медстат;
        $tables = Table::OfForm($form->id)->whereNotNull('medstat_code')->whereDoesntHave('excluded', function ($query) use($default_album) {
            $query->where('album_id', $default_album->id);
        })->orderBy('table_index')->get();
        foreach ($tables as $table) {
            $count_ms = 0;
            $errors[$table->id] = [];
            //if (!$table->medstat_code) break;
            $rows = Row::OfTable($table->id)->whereNotNull('medstat_code')->whereDoesntHave('excluded', function ($query) use($default_album) {
                $query->where('album_id', $default_album->id);
            })->orderBy('row_index')->get();
            $i = 0;
            $count_mi = count($rows);
            foreach($rows as $row) {
                $matching_array[$table->id][$i][0] = $row->row_index;
                $matching_array[$table->id][$i][1] = $row->row_code;
                $matching_array[$table->id][$i][2] = $row->row_name;
                $matching_array[$table->id][$i][3] = $row->medstat_code;
                $i++;
            }

            if (!$table->transposed ) {
                $q_medstat = "SELECT * FROM ms_str WHERE a1 like '{$form->medstat_code}{$table->medstat_code}%' ORDER BY rec_id";
                $ms_rows = \DB::select($q_medstat);
                $count_ms = count($ms_rows);
                if ($count_ms == 0) {
                    $errors[$table->id][] = 'В словаре Медстат отстутствуют строки по данной таблице';
                } else {
                    $j = 0;
                    foreach($ms_rows as $ms_row) {
                        $matching_array[$table->id][$j][4] = $ms_row->a1;
                        $matching_array[$table->id][$j][5] = $ms_row->a2;
                        $matching_array[$table->id][$j][6] = $ms_row->gt;
                        $matching_array[$table->id][$j][7] = substr($ms_row->a1, -3);
                        $j++;
                    }
                }

                foreach ($matching_array[$table->id] as $matched_rows) {
                    if (!isset($matched_rows[7]) || !isset($matched_rows[3]) || $matched_rows[7] !== $matched_rows[3]) {
                        if (isset($matched_rows[1])) {
                            $errors[$table->id][] = 'Не совпадает код по строке Мединфо' . $matched_rows[1]  . '!';
                        } else {
                            $errors[$table->id][] = 'Не совпадает код по строке Медстат' . $matched_rows[7]  . '!';
                        }
                        //$errors[$table->id][] = 'Не совпадает код по строке' ;
                    }
                }

                //dd($matching_array);
            } elseif ($table->transposed == 1) {
                $q_medstat = "SELECT * FROM ms_grf WHERE a1 like '{$form->medstat_code}{$table->medstat_code}%' ORDER BY a1";
                $ms_grfs = \DB::select($q_medstat);
                $count_ms = count($ms_grfs);
                if ($count_ms == 0) {
                    $errors[$table->id][] = 'В словаре Медстат отстутствуют графы по данной (транспонированной) таблице';
                } else {
                    $j = 0;
                    foreach($ms_grfs as $ms_row) {
                        $matching_array[$table->id][$j][4] = $ms_row->a1;
                        $matching_array[$table->id][$j][5] = $ms_row->a2;
                        $matching_array[$table->id][$j][6] = $ms_row->gt;
                        $matching_array[$table->id][$j][7] = substr($ms_row->a1, -2);
                        $j++;
                    }

                }
                foreach ($matching_array[$table->id] as $matched_rows) {
                    //dd($matched_rows[1]);
                    if (!isset($matched_rows[7]) || !isset($matched_rows[3]) || '0' . $matched_rows[7] !== $matched_rows[3]) {

                        if (isset($matched_rows[1])) {
                            $errors[$table->id][] = 'Не совпадает код по строке Мединфо' . $matched_rows[1]  . '!';
                        } else {
                            $errors[$table->id][] = 'Не совпадает код по строке Медстат' . $matched_rows[7]  . '!';
                        }

                    }
                }
            }

            if ($count_ms <> $count_mi) {
                $errors[$table->id][] = 'Не совпадает количество строк!';
            }
        }
        return view('jqxdatainput.medstatprotocol', compact('form', 'tables', 'matching_array', 'errors', 'mode'));
    }
    // Сопоставление граф в Мединфо и в Медстат
    public function columnsMatching($formcode)
    {
        $matching_array = [];
        $errors = [];
        $mode = 'графы';
        $default_album = Album::Default()->first(['id']);
        $form = Form::OfCode($formcode)->first();
        // обрабатываем таблицы из Мединфо не исключенные из текущего альбома и имеющие код Медстат;
        $tables = Table::OfForm($form->id)->where('transposed', 0)->whereNotNull('medstat_code')->whereDoesntHave('excluded', function ($query) use($default_album) {
            $query->where('album_id', $default_album->id);
        })->orderBy('table_index')->get();
        foreach ($tables as $table) {
            $count_ms = 0;
            $errors[$table->id] = [];
            //if (!$table->medstat_code) break;

            $columns = Column::OfTable($table->id)->OfDataType()->whereDoesntHave('excluded', function ($query) use($default_album) {
                $query->where('album_id', $default_album->id);
            })->orderBy('column_index')->get();
            $i = 0;
            $count_mi = count($columns);
            foreach($columns as $column) {
                $matching_array[$table->id][$i][0] = $column->column_index;
                $matching_array[$table->id][$i][1] = $column->column_index;
                $matching_array[$table->id][$i][2] = $column->column_name;
                $matching_array[$table->id][$i][3] = $column->medstat_code;
                $i++;
            }

            $q_medstat = "SELECT * FROM ms_grf WHERE a1 LIKE '{$form->medstat_code}{$table->medstat_code}%' ORDER BY a1";
            $ms_columns = \DB::select($q_medstat);
            $count_ms = count($ms_columns);
            if ($count_ms == 0) {
                $errors[$table->id][] = 'В словаре Медстат отстутствуют графы по данной таблице';
            } else {
                $j = 0;
                foreach($ms_columns as $ms_column) {
                    $matching_array[$table->id][$j][4] = $ms_column->a1;
                    $matching_array[$table->id][$j][5] = $ms_column->a2;
                    $matching_array[$table->id][$j][6] = $ms_column->gt;
                    $matching_array[$table->id][$j][7] = substr($ms_column->a1, -2);
                    $j++;
                }
            }

            foreach ($matching_array[$table->id] as $matched_rows) {
                if (!isset($matched_rows[7]) || !isset($matched_rows[3]) || $matched_rows[7] !== $matched_rows[3]) {
                    if (isset($matched_rows[1])) {
                        $errors[$table->id][] = 'Не совпадает код по графе Мединфо' . $matched_rows[1]  . '!';
                    } else {
                        $errors[$table->id][] = 'Не совпадает код по графе Медстат' . $matched_rows[7]  . '!';
                    }

                    //$errors[$table->id][] = 'Не совпадает код по строке' ;
                }
            }

                //dd($matching_array);

            if ($count_ms <> $count_mi) {
                $errors[$table->id][] = 'Не совпадает количество граф!';
            }
        }
        return view('jqxdatainput.medstatprotocol', compact('form', 'tables', 'matching_array', 'errors', 'mode'));
    }

}
