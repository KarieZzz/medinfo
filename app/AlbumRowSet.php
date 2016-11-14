<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Album;

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

    public static function setRow($excluded = false, int $row)
    {
        $default_album = Album::Default()->first()->id;
        if (!$excluded) {
            $proccessed = self::where('album_id', $default_album)->where('row_id', $row)->delete();
        } else {
            $proccessed = self::firstOrCreate([ 'album_id' => $default_album, 'row_id' => $row ]);
        }
        return $proccessed;
    }

}
