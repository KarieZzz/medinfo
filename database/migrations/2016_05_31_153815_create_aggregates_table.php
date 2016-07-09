<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAggregatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('aggregates', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('ou_id')->index();
            $table->char('period_id', 8)->index();
            $table->integer('form_id')->index();
            $table->string('include_docs', 2048);
            $table->softDeletes();
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
        Schema::drop('aggregates');
    }
}
