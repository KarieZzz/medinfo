<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Form extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'form_code', 'form_name', 'form_index', 'file_name', 'medstat_code', 'medinfo_id',
    ];

    public function tables()
    {
        return $this->hasMany('App\Table');
    }

    public function scopeOfCode($query, $code)
    {
        return $query
            ->where('form_code', $code);
    }

    public function included()
    {
        return $this->hasMany('App\AlbumFormSet');
    }

}
