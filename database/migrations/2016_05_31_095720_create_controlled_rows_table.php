<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateControlledRowsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('controlled_rows', function (Blueprint $table) {
            $table->integer('form_id')->index();
            $table->integer('table_id')->index();
            $table->integer('row_id')->index();
            $table->integer('control_scope')->index();
            $table->integer('relation')->index();
            $table->timestamp('sync');
            $table->primary(['form_id', 'table_id', 'row_id', 'relation']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('controlled_rows');
    }
}
