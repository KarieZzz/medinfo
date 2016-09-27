<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUnitGroupMembersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create('unit_group_members', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('group_id')->index();
            $table->integer('ou_id')->index();
            $table->unique(['group_id', 'ou_id']);
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
        Schema::drop('unit_group_members');
    }
}
