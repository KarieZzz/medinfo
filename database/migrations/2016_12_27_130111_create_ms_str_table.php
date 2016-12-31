<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMsStrTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ms_str', function (Blueprint $table) {
            $table->integer('rec_id')->primary();
            $table->char('a1', 12)->index();
            $table->string('a2', 128);
            $table->char('gt', 2);
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
        Schema::drop('ms_str');
    }
}
