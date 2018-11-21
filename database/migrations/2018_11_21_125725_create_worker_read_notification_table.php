<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWorkerReadNotificationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create('worker_read_notifications', function (Blueprint $table) {
            $table->uuid('uid')->primary();
            $table->integer('worker_id')->index();
            $table->integer('event_type')->index();
            $table->timestamp('occured_at');
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
        Schema::drop('worker_read_notifications');
    }
}
