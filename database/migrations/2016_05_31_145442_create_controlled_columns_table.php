<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateControlledColumnsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('controlled_columns', function (Blueprint $table) {
            $table->integer('rec_id')->primary();
            $table->integer('ol4_')->index();
            $table->integer('ol4')->index();
            $table->integer('fr');
            $table->integer('fk');
            $table->timestamp('sync');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('controlled_columns');
    }
}
