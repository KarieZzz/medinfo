<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCfunctionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cfunctions', function (Blueprint $table) {
            //
            $table->increments('id');
            $table->integer('table_id')->index();
            $table->smallInteger('level')->index();
            $table->smallInteger('type')->index();
            $table->smallInteger('function')->index();
            $table->string('script', 512);
            $table->string('comment', 128)->nullable();
            $table->boolean('blocked')->default(false);
            $table->text('compiled_cashe')->nullable();
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
        Schema::drop('cfunctions');
    }
}
