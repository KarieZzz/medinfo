<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AlbumColumnSet extends Model
{
    //
    protected $table = 'album_columns';
    protected $fillable = ['album_id', 'column_id'];

    public function scopeOfAlbum($query, $album)
    {
        return $query
            ->where('album_id', $album);
    }

}
