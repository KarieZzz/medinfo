<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableMedstatNskControls extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create('medstat_nsk_controls', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('form')->index();
            $table->string('table', 10)->nullable()->index();
            $table->integer('error_type');
            $table->string('left', 255);
            $table->string('right', 255);
            $table->string('relation', 5);
            $table->string('cycle', 255)->nullable();
            $table->string('comment', 255)->nullable();
            $table->string('converted', 512)->nullable();
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
        Schema::drop('medstat_nsk_controls');
    }
}
