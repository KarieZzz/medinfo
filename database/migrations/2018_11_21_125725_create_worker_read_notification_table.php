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
            $table->increments('id');
            $table->integer('worker_id')->index();
            $table->uuid('event_uid')->index();
            $table->integer('event_type')->default(1)->index();
            $table->timestamp('occured_at');
            $table->unique(['worker_id', 'event_uid']);
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
