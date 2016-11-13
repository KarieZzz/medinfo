<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Album;
use App\AlbumFormSet;

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
        return Album::all();
    }

    public function fetchFormSet(int $album)
    {
        return AlbumFormSet::OfAlbum($album)->get();
    }

    public function store(Request $request)
    {
        $this->validate($request, [
                'album_name' => 'required|unique:albums',
            ]
        );
        try {
            $newalbum = Album::create($request->all());
            return ['message' => 'Новая запись создана. Id:' . $newalbum->id];
        } catch (\Illuminate\Database\QueryException $e) {
            return($e->errorInfo[0]);
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
            $album->default = $request->default;
            $album->save();
            return ['message' => 'Изменения в альбоме сохранены. Id:' . $album->id];
        } catch (\Illuminate\Database\QueryException $e) {
            return($e->errorInfo[0]);
        }
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
