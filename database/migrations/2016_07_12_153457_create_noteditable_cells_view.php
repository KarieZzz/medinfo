<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNoteditableCellsView extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        DB::statement('CREATE VIEW noteditable_cells_view AS
            SELECT
                f.id AS f,
                t.id AS t,
                n.row_id AS r,
                n.column_id AS c
            FROM
                noteditable_cells n
                JOIN rows r ON r.id = n.row_id
                JOIN columns c ON c.id = n.column_id
                JOIN tables t ON t.id = r.table_id
                JOIN forms f ON f.id = t.form_id');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        DB::statement('DROP VIEW noteditable_cells_view');
    }
}
