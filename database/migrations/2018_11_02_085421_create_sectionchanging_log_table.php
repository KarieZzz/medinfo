<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSectionchangingLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create('sectionchanging_log', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('worker_id')->index();
            $table->integer('document_id')->index();
            $table->integer('section_id')->index();
            $table->boolean('blocked')->index();
            $table->timestamp('occured_at');
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
        Schema::drop('sectionchanging_log');
    }
}
