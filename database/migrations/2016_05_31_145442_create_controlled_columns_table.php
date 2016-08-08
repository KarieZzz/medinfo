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
            $table->integer('controlled')->index();
            $table->integer('controlling')->index();
            $table->integer('boolean_sign');
            $table->integer('number_sign');
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
