<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateConsolidatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create('consolidates', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('doc_id')->index();
            $table->integer('row_id')->index();
            $table->integer('column_id')->index();
            $table->jsonb('protocol');
            $table->timestamp('consolidated_at')->index();
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
        Schema::drop('consolidates');
    }
}
