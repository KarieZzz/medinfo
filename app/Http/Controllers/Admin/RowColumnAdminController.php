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
        $forms = Form::orderBy('form_index')->get(['id', 'form_code']);
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
        }])->get();
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
                'row_index' => 'integer',
                'row_name' => 'required|max:256',
                'row_code' => 'required|max:16',
                'medstat_code' => 'digits:3',
                'medinfo_id' => 'integer',
                'excluded' => 'required|in:1,0',
            ]
        );
        $row->row_index = $request->row_index;
        $row->row_code = $request->row_code;
        $row->row_name = $request->row_name;
        $row->medstat_code = empty($request->medstat_code) ? null : $request->medstat_code;
        $row->medinfo_id = empty($request->medinfo_id) ? null : $request->medinfo_id;
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
            // duplicate key value - код ошибки 7 при использовании PostgreSQL
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
                'row_index' => 'integer',
                'row_name' => 'required|max:256',
                'row_code' => 'required|max:16',
                'medstat_code' => 'digits:3',
                'medinfo_id' => 'integer',
                'excluded' => 'required|in:1,0',
            ]
        );
        $newrow = new Row;
        $newrow->table_id = $request->table_id;
        $newrow->row_index = $request->row_index;
        $newrow->row_code = $request->row_code;
        $newrow->row_name = $request->row_name;
        $newrow->medstat_code = empty($request->medstat_code) ? null : $request->medstat_code;
        $newrow->medinfo_id = empty($request->medinfo_id) ? null : $request->medinfo_id;
        try {
            $newrow->save();
            return ['message' => 'Новая запись создана. Id:' . $newrow->id];
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
        $cell_count = Cell::countOfCellsByRow($row->id);
        if ($cell_count == 0) {
            $row->delete();
            return ['message' => 'Удалена строка Id' . $row->id ];
        } else {
            return ['error' => 422, 'message' => 'Строка Id' . $row->id . ' содержит данные. Удаление невозможно.' ];
        }
    }

    public function columnUpdate(Column $column, Request $request)
    {
        $this->validate($request, [
                'column_index' => 'digits_between:1,99',
                'column_name' => 'required|max:128',
                'content_type' => 'integer',
                'size' => 'integer',
                'decimal_count' => 'integer',
                'medstat_code' => 'digits:2',
                'medinfo_id' => 'integer',
                'excluded' => 'required|in:1,0',
            ]
        );
        $column->column_index = $request->column_index;
        $column->column_name = $request->column_name;
        $column->content_type = $request->content_type;
        $column->size = $request->size;
        $column->decimal_count = $request->decimal_count;
        $column->medstat_code = empty($request->medstat_code) ? null : $request->medstat_code;
        $column->medinfo_id = empty($request->medinfo_id) ? null : $request->medinfo_id;
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
            // duplicate key value - код ошибки 7 при использовании PostgreSQL
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
                'column_index' => 'digits_between:1,99',
                'column_name' => 'required|max:128',
                'content_type' => 'integer',
                'size' => 'integer',
                'decimal_count' => 'integer',
                'medstat_code' => 'digits:2',
                'medinfo_id' => 'integer',
                'excluded' => 'required|in:1,0',
            ]
        );
        $newcolumn = new Column;
        $newcolumn->table_id = $request->table_id;
        $newcolumn->column_index = $request->column_index;
        $newcolumn->column_name = $request->column_name;
        $newcolumn->content_type = $request->content_type;
        $newcolumn->size = $request->size;
        $newcolumn->decimal_count = $request->decimal_count;
        $newcolumn->medstat_code = empty($request->medstat_code) ? null : $request->medstat_code;
        $newcolumn->medinfo_id = empty($request->medinfo_id) ? null : $request->medinfo_id;
        try {
            $newcolumn->save();
            return ['message' => 'Новая запись создана. Id:' . $newcolumn->id];
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

    public function columnDelete(Column $column)
    {
        $cell_count = Cell::countOfCellsByRow($column->id);
        if ($cell_count == 0) {
            $column->delete();
            return ['message' => 'Удалена графа Id' . $column->id ];
        } else {
            return ['error' => 422, 'message' => 'Графа Id' . $column->id . ' содержит данные. Удаление невозможно.' ];
        }
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
        })->get();
        foreach ($tables as $table) {
            $count_ms = 0;
            $errors[$table->id] = [];
            //if (!$table->medstat_code) break;

            $rows = Row::OfTable($table->id)->whereDoesntHave('excluded', function ($query) use($default_album) {
                $query->where('album_id', $default_album->id);
            })->get();
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
        })->get();
        foreach ($tables as $table) {
            $count_ms = 0;
            $errors[$table->id] = [];
            //if (!$table->medstat_code) break;

            $columns = Column::OfTable($table->id)->OfDataType()->whereDoesntHave('excluded', function ($query) use($default_album) {
                $query->where('album_id', $default_album->id);
            })->get();
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
