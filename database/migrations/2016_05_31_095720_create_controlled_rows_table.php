<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateControlledRowsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('controlled_rows', function (Blueprint $table) {
            $table->integer('nl1');
            $table->integer('nl2')->index();
            $table->integer('ol3')->index();
            $table->integer('ol5');
            $table->integer('fmk');
            $table->integer('rl1235')->index();
            $table->timestamp('sync');
            $table->primary(['nl1', 'nl2', 'ol3', 'ol5', 'fmk']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('controlled_rows');
    }
}
