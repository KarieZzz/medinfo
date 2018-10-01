<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateColumnsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('columns', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('table_id')->index();
            $table->integer('column_index')->index();
            $table->string('column_code', 8)->index();
            $table->string('column_name', 256)->index();
            $table->smallInteger('content_type')->default(4)->index();
            $table->smallInteger('size')->default(10);
            $table->smallInteger('decimal_count');
            $table->char('medstat_code', 2)->nullable()->index();
            $table->integer('medstatnsk_id')->nullable()->index();
            $table->smallInteger('deleted')->default(0);
            $table->integer('deleted_at')->nullable();
            $table->timestamps();
            $table->unique(['table_id', 'column_code', 'column_name']);
            $table->unique(['table_id', 'column_index']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('columns');
    }
}
