<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWorkerScopesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('worker_scopes', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('worker_id')->unique();
            $table->integer('ou_id')->index();
            $table->integer('with_descendants');
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
        Schema::drop('worker_scopes');
    }
}
