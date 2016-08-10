<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTablesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create('tables', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('form_id')->index();
            $table->string('table_code', 6)->index();
            $table->string('table_name', 256)->index();
            $table->char('medstat_code', 4)->nullable()->index();
            $table->integer('medinfo_id')->index();
            $table->integer('transposed');
            $table->integer('aggregated_column_id');
            $table->integer('deleted');
            $table->integer('deleted_at')->nullable();
            //$table->softDeletes();
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
        Schema::drop('tables');
    }
}
