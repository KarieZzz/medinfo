<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAuditChangeLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('audit_change_log', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('doc_id')->index();
            $table->integer('user_id')->index();
            $table->integer('old_state')->index();
            $table->integer('new_state')->index();
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
        Schema::drop('audit_change_log');
    }
}
