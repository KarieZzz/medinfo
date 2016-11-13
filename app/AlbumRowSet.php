<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AlbumRowSet extends Model
{
    //
    protected $table = 'album_rows';
    protected $fillable = ['album_id', 'row_id'];

    public function scopeOfAlbum($query, $album)
    {
        return $query
            ->where('album_id', $album);
    }

}
