<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDocumentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('ou_id')->index();
            $table->char('period_id', 8)->index();
            $table->integer('form_id')->index();
            $table->integer('state')->index();
            $table->integer('state_changed_by_user')->index();
            $table->timestamp('state_changed_at')->nullable()->index();
            $table->softDeletes();
            $table->timestamps();
            $table->unique(['ou_id', 'period_id', 'form_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('documents');
    }
}
