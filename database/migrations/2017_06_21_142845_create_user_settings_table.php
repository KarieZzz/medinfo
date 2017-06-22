<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create('worker_settings', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('worker_id')->index();
            $table->string('name', 256);
            $table->text('value')->nullable();
            $table->timestamps();
            $table->unique(['worker_id', 'name']);
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
        Schema::drop('worker_settings');
    }
}
