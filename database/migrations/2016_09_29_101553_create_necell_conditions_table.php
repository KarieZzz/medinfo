<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNecellConditionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('necell_conditions', function (Blueprint $table) {
            //
            $table->increments('id');
            $table->string('condition_name', 128)->unique();
            $table->integer('group_id')->index();
            $table->boolean('exclude')->default(false);
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
        Schema::drop('necell_conditions');
    }
}
