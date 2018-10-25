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

    public function scopeOfColumn($query, $column)
    {
        return $query
            ->where('column_id', $column);
    }

    public static function setColumn($excluded = false, int $column)
    {
        $default_album = Album::Default()->first()->id;
        if (!$excluded) {
            $proccessed = self::where('album_id', $default_album)->where('column_id', $column)->delete();
        } else {
            $proccessed = self::firstOrCreate([ 'album_id' => $default_album, 'column_id' => $column ]);
        }
        return $proccessed;
    }

}
