<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNoteditableCellsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('noteditable_cells', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('row_id')->index();
            $table->integer('column_id')->index();
            $table->timestamps();
            $table->unique(['row_id', 'column_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('noteditable_cells');
    }
}
