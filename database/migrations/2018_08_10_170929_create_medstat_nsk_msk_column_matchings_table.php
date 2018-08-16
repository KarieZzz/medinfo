<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMedstatNskMskColumnMatchingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create('medstat_nsk_msk_column_matchings', function (Blueprint $table) {
            $table->increments('id');
            $table->string('mdstable', 50)->index();
            $table->integer('mdscol')->index();
            $table->char('mskcol', 2)->index();
            $table->boolean('transposed')->nullable();
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
        Schema::drop('medstat_nsk_msk_column_matchings');
    }
}
