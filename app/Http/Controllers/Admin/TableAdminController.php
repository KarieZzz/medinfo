<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Album;
use App\AlbumTableSet;
use App\Form;
use App\Table;
use App\Cell;

class TableAdminController extends Controller
{
    //
    public function __construct()
    {
        $this->middleware('admins');
    }

    public function index()
    {
        $default_album = $this->getDefaultAlbum();
        $forms = Form::whereHas('included', function ($query) use($default_album) {
            $query->where('album_id', $default_album->id);
        })->orderBy('form_index')->get(['id', 'form_code']);
        return view('jqxadmin.tables', compact('forms', 'default_album'));
    }

    public function getDefaultAlbum()
    {
        $default_album = Album::Default()->first();
        if (is_null($default_album)) {
            $default_album = Album::find(config('medinfo.default_album'));
        }
        return $default_album;
    }

    public function fetchTables()
    {
        $default_album = $this->getDefaultAlbum();
        $forms = Form::whereHas('included', function ($query) use($default_album) {
            $query->where('album_id', $default_album->id);
        })->orderBy('form_index')->pluck('id');
        return Table::whereIn('form_id', $forms)->orderBy('form_id')->orderBy('table_index')->with('form')->with(['excluded' => function ($query) use ($default_album) {
            $query->where('album_id', $default_album->id);
        }])->get();

        //return Form::all();
    }

    public function store(Request $request)
    {
        $this->validate($request, [
                'form_id' => 'required|integer|exists:forms,id',
                'table_name' => 'required',
                'table_code' => 'required',
                'medstat_code' => 'digits:4',
                'medstatnsk_id' => 'integer',
                'placebefore' => 'integer',
            ]
        );
        if ($request->table_index === '' ) {
            //dd($request->table_index);
            $new_table_index = Table::OfForm($request->form_id)->count();
        } else {
            $new_table_index = $request->table_index;
        }

        $newtable = new Table;
        $newtable->form_id = $request->form_id;
        $newtable->table_index = $new_table_index;
        $newtable->table_code = $request->table_code;
        $newtable->table_name = $request->table_name;
        $newtable->medstat_code = empty($request->medstat_code) ? null : $request->medstat_code;
        $newtable->medstatnsk_id = empty($request->medstatnsk_id) ? null : $request->medstatnsk_id;
        $newtable->transposed = $request->transposed;
/*        $newtable->save();
        if ($request->placebefore !== '') {
            $this->placebefore($newtable, $request->placebefore);
            $newtable->table_index = $request->placebefore;
            $newtable->save();
        }*/

        try {
            $newtable->save();
            if ($request->placebefore !== '') {
                $this->placebefore($newtable, $request->placebefore);
                $newtable->table_index = $request->placebefore;
                $newtable->save();
            }
            return ['message' => 'Новая запись создана. Id:' . $newtable->id];
        } catch (\Illuminate\Database\QueryException $e) {
            $errorCode = $e->errorInfo[0];
            switch ($errorCode) {
                case '23505':
                    $message = 'Запись не сохранена. Дублирование данных.';
                    break;
                default:
                    $message = 'Новая запись не создана. Код ошибки ' . $errorCode . '.';
                    break;
            }
            return ['error' => 422, 'message' => $message];
        }
    }

    public function update(Table $table, Request $request)
    {
        $this->validate($request, [
                'table_name' => 'required',
                'table_index' => 'integer',
                'transposed' => 'boolean',
                'medstat_code' => 'digits:4',
                'medstatnsk_id' => 'integer',
                'excluded' => 'required|in:1,0',
            ]
        );
        //$table = Table::find($request->id);
        $table->form_id = $request->form_id;
        $table->table_index = $request->table_index;
        $table->table_code = $request->table_code;
        $table->table_name = $request->table_name;
        $table->medstat_code = empty($request->medstat_code) ? null : $request->medstat_code;
        $table->medstatnsk_id = empty($request->medstatnsk_id) ? null : $request->medstatnsk_id;
        $table->transposed = $request->transposed;
        $result = [];
        try {
            $table->save();
            $exclude = AlbumTableSet::excludeTable($request->excluded, $table->id);
            $add = '';
            if ($exclude === 1) {
                $add = "Таблица удалена из списка исключенных в текущем альбоме форм";
            }
            $result = ['message' => 'Запись id ' . $table->id . ' сохранена. ' . $add];
        } catch (\Illuminate\Database\QueryException $e) {
            $errorCode = $e->errorInfo[0];
            if($errorCode == '23505'){
                $result = ['error' => 422, 'message' => 'Запись не сохранена. Дублирование данных.'];
            }
        }
        return $result;
    }

    public function delete(Table $table)
    {
        $cell_count = Cell::countOfCellsByTable($table->id);
        if ($cell_count === 0) {
            $rdeleted = \App\Row::OfTable($table->id)->delete();
            $cdeleted = \App\Column::OfTable($table->id)->delete();
            $table->delete();
            return ['message' => "Удалена таблица Id {$table->id}, удалено строк: $rdeleted, граф: $cdeleted " ];

        } else {
            return ['error' => 422, 'message' => 'Таблица Id' . $table->id . ' содержит данные. Удаление невозможно.' ];
        }
    }

    public function up(Table $table)
    {
        $current_index = $table->table_index;
        //dd($current_index-1);
        $prevtable = Table::OfForm($table->form_id)->where('table_index', $current_index - 1)->first();
        //dd($prevtable);
        if(is_null($prevtable)){
            return ['error' => 422, 'message' => 'Выше некуда.'];
        }
        $table->table_index = $current_index - 1;
        $table->save();
        $prevtable->table_index = $current_index;
        $prevtable->save();
        return [$current_index, $current_index - 1];
    }

    public function down(Table $table)
    {
        $current_index = $table->table_index;
        //dd($current_index-1);
        $nexttable = Table::OfForm($table->form_id)->where('table_index', $current_index + 1)->first();
        //dd($prevtable);
        if(is_null($nexttable)){
            return ['error' => 422, 'message' => 'Дальше некуда.'];
        }
        $table->table_index = $current_index + 1;
        $table->save();
        $nexttable->table_index = $current_index;
        $nexttable->save();
        return [$current_index, $current_index + 1];
    }

    public function top(Table $table, $top = 1)
    {
        $current_index = $table->table_index;
        //dd($current_index-1);
        $uppertables = Table::OfForm($table->form_id)->where('table_index', '<' ,$current_index)->get();
        //dd($uppertables->count());
        if ($uppertables->count() === 0) {
            return ['error' => 422, 'message' => 'Выбранная запись уже в начале списка.'];
        }
        foreach ($uppertables as $uppertable) {
            $uppertable->table_index = $uppertable->table_index + 1;
            $uppertable->save();
            //dump($uppertable->table_index);
        }
        $table->table_index = $top;
        $table->save();
        return [$current_index, $top];
    }

    public function bottom(Table $table, $top = 1)
    {
        $current_index = $table->table_index;
        //dd($current_index-1);
        $belowtables = Table::OfForm($table->form_id)->where('table_index', '>' ,$current_index)->get();
        if ($belowtables->count() === 0) {
            return ['error' => 422, 'message' => 'Выбранная запись уже в конце списка.'];
        }
        //dd($uppertables);
        foreach ($belowtables as $belowtable) {
            $belowtable->table_index = $belowtable->table_index - 1 ;
            $belowtable->save();
            //dump($uppertable->table_index);
        }
        $table->table_index = $belowtable->table_index + 1;
        $table->save();
        return [$current_index, $table->table_index];
    }

    public function placebefore(Table $table, $index)
    {
        if (!$index) {
            return null;
        }
        $belowtables = Table::OfForm($table->form_id)->where('table_index', '>=', $index)->get();
        if ($belowtables->count() === 0) {
            throw new \Exception("Заданный порядковый номер не существует");
        }
        foreach ($belowtables as $belowtable) {
            $belowtable->table_index = $belowtable->table_index + 1 ;
            $belowtable->save();
        }
        return true;
    }
}
