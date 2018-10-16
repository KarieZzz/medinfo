<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDocumentSectionBlocksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create('document_section_blocks', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('formsection_id')->index();
            $table->integer('document_id')->index();
            $table->integer('worker_id')->index();
            $table->boolean('blocked')->index();
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
        //
        Schema::drop('document_section_blocks');
    }
}
