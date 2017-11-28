<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UnitListMembersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create('unit_list_members', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('list_id')->index();
            $table->integer('ou_id')->index();
            $table->unique(['list_id', 'ou_id']);
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
        Schema::drop('unit_list_members');
    }
}
