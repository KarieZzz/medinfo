<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UnitListsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create('unit_lists', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 128)->unique();
            $table->string('slug', 32)->unique();
            $table->smallInteger('on_frontend')->index();
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
        Schema::drop('unit_lists');
    }
}
