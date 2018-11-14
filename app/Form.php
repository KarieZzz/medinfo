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
        'form_code', 'form_name', 'form_index', 'file_name', 'medstat_code', 'short_ms_code', 'relation', 'medstatnsk_id',
    ];

    public function tables()
    {
        return $this->hasMany('App\Table');
    }

    public function included()
    {
        return $this->hasMany('App\AlbumFormSet');
    }

    public function hasRelations()
    {
        return $this->hasMany('App\Form', 'relation' , 'id');
    }

    public function inheritFrom()
    {
        return $this->belongsTo('App\Form', 'relation' , 'id');
    }

    public function scopeOfCode($query, $code)
    {
        return $query->where('form_code', $code);
    }

    public function scopeReal($query)
    {
        return $query->whereNull('relation');
    }

    public function scopeRelated($query)
    {
        return $query->whereNotNull('relation');
    }

    public function scopeOfMedstatCode($query, $code)
    {
        return $query->where('medstat_code', $code);
    }

    public function scopeNotOfMedstat($query)
    {
        return $query->whereNull('medstat_code');
    }

    public function scopeHasMedstatNSK($query)
    {
        return $query->whereNotNull('medstatnsk_id');
    }

    public function scopeNSK($query, $id)
    {
        return $query->where('medstatnsk_id', $id);
    }

    public static function getRealForm($form_id)
    {
        $form = Form::find($form_id);
        if ($form->relation) {
            return Form::find($form->relation);
        } else {
            return $form;
        }
    }

}
