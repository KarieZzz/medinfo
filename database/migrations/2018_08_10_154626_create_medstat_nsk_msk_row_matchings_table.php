<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMedstatNskMskRowMatchingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create('medstat_nsk_msk_row_matchings', function (Blueprint $table) {
            $table->increments('id');
            $table->string('mdstable', 50)->index();
            $table->integer('mdsrow')->index();
            $table->char('mskrow', 3)->index();
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
        Schema::drop('medstat_nsk_msk_row_matchings');
    }
}
