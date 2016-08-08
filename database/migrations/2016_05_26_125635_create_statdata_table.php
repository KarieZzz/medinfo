<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStatdataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create('statdata', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('doc_id')->index();
            $table->integer('table_id')->index();
            $table->integer('row_id')->index();
            $table->integer('col_id')->index();
            $table->decimal('value', 17,3)->nullable();
            $table->timestamps();
            $table->unique(['doc_id', 'row_id', 'col_id']);
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
        Schema::drop('statdata');
    }
}
