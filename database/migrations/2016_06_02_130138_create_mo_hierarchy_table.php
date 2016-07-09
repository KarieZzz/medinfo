<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMoHierarchyTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mo_hierarchy', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('parent_id')->index()->nullable();
            $table->string('unit_code', 32)->index();
            $table->char('inn', 10)->index();
            $table->integer('node_type')->index();
            $table->integer('report');
            $table->integer('aggregate');
            $table->string('unit_name', 256)->index();
            $table->integer('blocked');
            $table->softDeletes();
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
        Schema::drop('mo_hierarchy');
    }
}
