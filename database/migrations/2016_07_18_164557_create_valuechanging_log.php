<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateValuechangingLog extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create('valuechanging_log', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('worker_id')->index();
            $table->decimal('oldvalue', 17,3);
            $table->decimal('newvalue', 17,3);
            $table->integer('d')->index();
            $table->integer('o')->index();
            $table->integer('f')->index();
            $table->integer('t')->index();
            $table->integer('r')->index();
            $table->integer('c')->index();
            $table->char('p', 8)->index();
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
        Schema::drop('valuechanging_log');
    }
}
