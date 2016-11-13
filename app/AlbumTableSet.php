<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AlbumTableSet extends Model
{
    //
    protected $table = 'album_tables';
    protected $fillable = ['album_id', 'table_id'];

    public function scopeOfAlbum($query, $album)
    {
        return $query
            ->where('album_id', $album);
    }

}
