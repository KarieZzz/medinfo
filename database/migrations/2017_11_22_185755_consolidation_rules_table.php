<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ConsolidationRulesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create('consolidation_rules', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('row_id')->index();
            $table->integer('col_id')->index();
            $table->string('script', 512);
            $table->string('comment', 128)->nullable();
            $table->timestamps();
            $table->unique(['row_id', 'col_id']);
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
        Schema::drop('consolidation_rules');
    }
}
