<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStatechangingLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create('statechanging_log', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('worker_id')->index();
            $table->integer('document_id')->index();
            $table->integer('oldstate');
            $table->integer('newstate');
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
        Schema::drop('statechanging_log');
    }
}
