<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMedstatNskRowLinksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create('medstat_nsk_row_links', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('table')->index();
            $table->integer('row')->index();
            $table->char('medstat_code', 5)->nullable();
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
        Schema::drop('medstat_nsk_row_links');
    }
}
