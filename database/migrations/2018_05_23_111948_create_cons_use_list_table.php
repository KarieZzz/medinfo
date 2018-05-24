<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateConsUseListTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create('cons_use_lists', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('row_id')->index();
            $table->integer('col_id')->index();
            $table->integer('list')->index();
            $table->timestamps();
            $table->unique(['row_id', 'col_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        Schema::drop('cons_use_lists');
    }
}
