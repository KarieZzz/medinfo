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
        $this->middleware('auth');
    }

    public function index()
    {
        return view('jqxadmin.albums');
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
            ]
        );
        try {
            $newalbum = new Album;
            $newalbum->album_name = $request->album_name;
            $newalbum->default = ($request->default == 0 ? null : true);
            $newalbum->save();
            return ['message' => 'Новая запись создана. Id:' . $newalbum->id];
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

}
