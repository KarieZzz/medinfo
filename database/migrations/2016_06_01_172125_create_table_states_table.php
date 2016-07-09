<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableStatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('table_states', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('table_id')->index();
            $table->integer('user_id')->index();
            $table->text('state');
            $table->timestamps();
            $table->unique(['table_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('table_states');
    }
}
