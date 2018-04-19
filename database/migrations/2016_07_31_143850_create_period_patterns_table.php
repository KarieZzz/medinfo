<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePeriodPatternsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('period_patterns', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 64)->index();
            $table->integer('periodicity')->index();
            $table->char('begin', 5)->index();
            $table->char('end', 5)->index();
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
        Schema::drop('period_patterns');
    }
}
