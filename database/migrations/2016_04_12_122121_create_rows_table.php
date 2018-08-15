<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRowsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create('rows', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('table_id')->index();
            $table->integer('row_index')->index();
            $table->string('row_code', 16)->index();
            $table->string('row_name', 256)->index();
            $table->char('medstat_code', 3)->nullable()->index();
            $table->integer('medinfo_id')->nullable()->index();
            $table->integer('deleted');
            $table->integer('deleted_at')->nullable();
            //$table->softDeletes();
            $table->timestamps();
            $table->unique(['table_id', 'row_code', 'row_name']);
            $table->unique(['table_id', 'row_index']);
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
        Schema::drop('rows');
    }
}
