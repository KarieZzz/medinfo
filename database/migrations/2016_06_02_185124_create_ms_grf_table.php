<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMsGrfTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ms_grf', function (Blueprint $table) {
            $table->integer('rec_id')->primary();
            $table->char('a1', 11)->index();
            $table->string('a2', 128);
            $table->char('gt', 2);
            $table->char('a3', 1);
            $table->timestamp('syncronized_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('ms_grf');
    }
}
