<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWorkerProfilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create('worker_profiles', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('worker_id')->index();
            $table->string('tag', 256);
            $table->string('attribute', 256)->nullable();
            $table->text('value')->nullable();
            $table->timestamps();
            $table->unique(['worker_id', 'tag', 'attribute']);
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
        Schema::drop('worker_profiles');
    }
}
