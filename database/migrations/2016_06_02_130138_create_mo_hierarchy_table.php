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
            $table->string('unit_code', 32)->unique();
            $table->integer('territory_type')->index()->nullable(); // Дополнительное поле для сортировки по городу, району, округу
            $table->char('inn', 10)->nullable()->unique();
            $table->smallInteger('node_type')->default(3)->index();
            $table->smallInteger('report')->default(0);
            $table->smallInteger('aggregate')->default(0);
            $table->string('unit_name', 256)->index();
            $table->string('adress', 256)->nullable();
            $table->boolean('countryside')->nullable();
            $table->smallInteger('blocked')->default(0);
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
