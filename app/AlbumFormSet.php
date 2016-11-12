<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AlbumFormSet extends Model
{
    //
    protected $table = 'album_forms';
    protected $fillable = ['album_id', 'form_id'];

    public function scopeOfAlbum($query, $album)
    {
        return $query
            ->where('album_id', $album);
    }

}
