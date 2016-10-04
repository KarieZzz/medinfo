<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class NECell extends Model
{
    //
    protected $table = 'noteditable_cells';
    protected $fillable = ['row_id' , 'column_id', 'condition_id'];

    public function scopeOfRC($query, $row, $column)
    {
        return $query
            ->where('row_id', $row)
            ->where('column_id', $column);
    }

/*    public static function isNotEditable(int $row, int $column)
    {
        $noteditable = false;
        //if (count((NECell::OfRC($row, $column)->pluck('id')))) {
        if ((NECell::OfRC($row, $column)->exists())) {
            $noteditable = true;
        }
        return $noteditable;
    }*/
}
