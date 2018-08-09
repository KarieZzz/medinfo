<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMedstatNskTableLinksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create('medstat_nsk_table_links', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('form_id')->index();
            $table->string('tablen', 10)->index();
            $table->string('name', 256);
            $table->integer('colcount');
            $table->integer('rowcount');
            $table->integer('fixcol');
            $table->integer('fixrows');
            $table->boolean('floattype');
            $table->integer('scan');
            $table->char('medstat_code', 9)->nullable();
            $table->timestamps();
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
        Schema::drop('medstat_nsk_table_links');
    }
}
