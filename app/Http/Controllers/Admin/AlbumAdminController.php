<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Album;
use App\AlbumFormSet;
use App\AlbumTableSet;
use App\AlbumRowSet;
use App\AlbumColumnSet;

class AlbumAdminController extends Controller
{
    //
    public function __construct()
    {
        $this->middleware('admins');
    }

    public function index()
    {
        $default_album = ($d = Album::Default()->first()) ? $d->id : config('medinfo.default_album');
        return view('jqxadmin.albums', compact('default_album'));
    }

    public function fetchAlbums()
    {
        return Album::orderBy('album_name')->get();
    }

    public function fetchFormSet(int $album)
    {
        return AlbumFormSet::OfAlbum($album)->with('form')->get();
    }

    public function store(Request $request)
    {
        $this->validate($request, [
                'album_name' => 'required|unique:albums',
                'migrate'   => 'in:1,0',
            ]
        );
        $default = ($d = Album::Default()->first()) ? $d->id : config('medinfo.default_album');
        $migrated = [];
        try {
            $newalbum = new Album;
            $newalbum->album_name = $request->album_name;
            if ($request->default === '1' ) {
                $newalbum->default = true;
                $old_default = Album::Default()->first();
                $old_default->default = null;
                $old_default->save();
            }
            $newalbum->save();
            if ($request->migrate === '1') {
                $migrated = $this->migrateAlbumSets($newalbum->id, $default);
            }
            return ['message' => 'Новая запись создана. Id:' . $newalbum->id, 'migrated' => $migrated, 'id' => $newalbum->id];
        } catch (\Illuminate\Database\QueryException $e) {
            return($this->error_message($e->errorInfo[0]));
        }
    }

    public function update(Album $album, Request $request)
    {
        $this->validate($request, [
                'album_name' => 'required',
            ]
        );
        try {
            $album->album_name = $request->album_name;
            if ($request->default === '1' ) {
                $album->default = true;
                $old_default = Album::Default()->first();
                if (!is_null($old_default)) {
                    $old_default->default = null;
                    $old_default->save();
                }
            }
            $album->default = ($request->default == 0 ? null : true);
            $album->save();
            return ['message' => 'Изменения в альбоме сохранены. Id:' . $album->id];
        } catch (\Illuminate\Database\QueryException $e) {
            return($this->error_message($e->errorInfo[0]));
        }
    }

    public function delete(Album $album)
    {
        $forms_deleted = AlbumFormSet::OfAlbum($album->id)->delete();
        $tables_deleted = AlbumTableSet::OfAlbum($album->id)->delete();
        $rows_deleted = AlbumRowSet::OfAlbum($album->id)->delete();
        $columns_deleted = AlbumColumnSet::OfAlbum($album->id)->delete();
        $records_deleted = $forms_deleted + $tables_deleted + $rows_deleted + $columns_deleted;
        $album->delete();
        return ['message' => 'Удален альбом Id' . $album->id . ". Связанных записей удалено: " . $records_deleted ];
    }

    public function addMembers(Album $album, Request $request)
    {
        $forms = explode(",", $request->forms);
        $newmembers = [];
        foreach($forms as $form) {
            $member = AlbumFormSet::firstOrCreate([ 'album_id' => $album->id, 'form_id' => $form ]);
            $newmembers[] = $member->id;
        }
        return [ 'count_of_inserted' => count($newmembers) ];
    }

    public function removeMember(AlbumFormSet $member)
    {
        $member_deleted = $member->delete();
        if ($member_deleted) {
            $message = "Выбранная форма удалена из списка";
        }
        return compact('member_deleted', 'message');
    }

    protected function error_message($errorCode)
    {
        switch ($errorCode) {
            case '23505':
                $message = 'Запись не сохранена. Дублирующиеся значения.';
                break;
            default:
                $message = 'Запись не сохранена. Код ошибки ' . $errorCode . '.';
                break;
        }
        return ['error' => 422, 'message' => $message];
    }

    public function migrateAlbumSets(int $toalbum, int $fromalbum)
    {
        $f = "INSERT INTO album_forms (id, album_id, form_id, created_at) 
          SELECT nextval('album_forms_id_seq'), $toalbum, form_id, current_timestamp 
          FROM album_forms af WHERE af.album_id = $fromalbum";
        $t = "INSERT INTO album_tables (id, album_id, table_id, created_at) 
          SELECT nextval('album_tables_id_seq'), $toalbum, table_id, current_timestamp 
          FROM album_tables at WHERE at.album_id = $fromalbum";
        $r = "INSERT INTO album_rows (id, album_id, row_id, created_at) 
          SELECT nextval('album_rows_id_seq'), $toalbum, row_id, current_timestamp 
          FROM album_rows ar WHERE ar.album_id = $fromalbum";
        $c = "INSERT INTO album_columns (id, album_id, column_id, created_at) 
          SELECT nextval('album_columns_id_seq'), $toalbum, column_id, current_timestamp 
          FROM album_columns ac WHERE ac.album_id = $fromalbum";
       $if = \DB::insert($f);
       $it = \DB::insert($t);
       $ir = \DB::insert($r);
       $ic = \DB::insert($c);
       return compact($if, $it, $ir, $ic);
    }
}
