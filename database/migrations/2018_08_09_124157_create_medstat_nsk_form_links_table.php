<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMedstatNskFormLinksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create('medstat_nsk_form_links', function (Blueprint $table) {
            $table->increments('id');
            $table->string('form_name', 50);
            $table->string('decipher', 256);
            $table->integer('ind')->index();
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
        Schema::drop('medstat_nsk_form_links');
    }
}
