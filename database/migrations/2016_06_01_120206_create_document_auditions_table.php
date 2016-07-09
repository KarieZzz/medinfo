<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDocumentAuditionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('document_auditions', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('doc_id')->index();
            $table->integer('user_id')->index();
            $table->integer('state_id')->index();
            $table->timestamps();
            $table->unique(['doc_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('document_auditions');
    }
}
