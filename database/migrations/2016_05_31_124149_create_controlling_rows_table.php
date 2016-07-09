<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateControllingRowsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('controlling_rows', function (Blueprint $table) {
            $table->integer('rl1235')->index();
            $table->integer('nl1')->index();
            $table->integer('nl2')->index();
            $table->integer('ol3')->index();
            $table->integer('ol5')->index();
            $table->integer('plf')->index();
            $table->integer('clf');
            $table->timestamp('sync');
            $table->primary(['rl1235', 'nl1', 'nl2', 'ol3']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('controlling_rows');
    }
}
