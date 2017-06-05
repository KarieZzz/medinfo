<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class MonitoringsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create('monitorings', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 256)->unique();
            $table->integer('periodicity')->index();
            $table->boolean('accumulation');
            $table->integer('album_id')->index();
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
        Schema::drop('monitorings');
    }
}
