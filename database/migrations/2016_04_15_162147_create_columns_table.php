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
            $table->string('column_name', 128)->index();
            $table->smallInteger('content_type');
            $table->smallInteger('value_type');
            $table->smallInteger('number_count');
            $table->smallInteger('decimal_count');
            $table->char('medstat_code', 4)->nullable()->index();
            $table->integer('medinfo_id')->nullable()->index();
            $table->integer('deleted');
            $table->integer('deleted_at')->nullable();
            $table->smallInteger('medinfo_type');
            $table->smallInteger('medinfo_size');
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
        Schema::drop('columns');
    }
}
