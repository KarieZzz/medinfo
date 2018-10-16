<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AlbumFormsection extends Model
{
    //
    protected $fillable = ['album_id', 'formsection_id'];

    public function formsection()
    {
        return $this->belongsTo('App\FormSection');
    }

    public function scopeOfAlbum($query, $album)
    {
        return $query
            ->where('album_id', $album);
    }

    public function scopeOfFormSection($query, $section)
    {
        return $query
            ->where('formsection_id', $section);
    }

}
