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

    public static function excludeTable($excluded = false, int $table)
    {
        $default_album = Album::Default()->first()->id;
        if (!$excluded) {
            $proccessed = self::where('album_id', $default_album)->where('table_id', $table)->delete();
        } else {
            $proccessed = self::firstOrCreate([ 'album_id' => $default_album, 'table_id' => $table ]);
        }
        return $proccessed;
    }

}
