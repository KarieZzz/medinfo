<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateControllingRowsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('controlling_rows', function (Blueprint $table) {
            $table->integer('relation')->index();
            $table->integer('form_id')->index();
            $table->integer('table_id')->index();
            $table->integer('row_id')->index();
            $table->integer('first_col')->index();
            $table->integer('count_col')->index();
            $table->integer('rec_id')->index();
            $table->timestamp('sync');
            $table->primary(['relation', 'form_id', 'table_id', 'row_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('controlling_rows');
    }
}
