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
            $table->integer('table_index');
            $table->string('table_code', 6)->nullable()->index();
            $table->string('table_name', 256)->index();
            $table->char('medstat_code', 4)->nullable()->index();
            $table->integer('medinfo_id')->index();
            $table->integer('transposed')->default(0);
            $table->integer('aggregated_column_id')->nullable();
            $table->integer('deleted')->default(0)->index();
            $table->string('comment', 256)->nullable();
            $table->softDeletes();
            $table->timestamps();
            $table->unique(['form_id', 'table_code']);
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
