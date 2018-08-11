<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMedstatNskDataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create('medstat_nsk_data', function (Blueprint $table) {
            $table->integer('hospital')->index();
            $table->float('data')->index();
            $table->integer('year')->index();
            $table->integer('table')->index();
            $table->integer('column')->index();
            $table->integer('row')->index();
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
        Schema::drop('medstat_nsk_data');
    }
}
