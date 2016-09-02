<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFormsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('forms', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('group_id')->index();
            $table->integer('form_index')->index();
            $table->string('form_code', 7)->index();
            $table->string('form_name', 256);
            $table->integer('form_index')->index();
            $table->string('file_name', 16)->nullable();
            $table->char('medstat_code', 5)->nullable();
            $table->integer('medinfo_id')->nullable()->index();
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
        Schema::drop('forms');
    }
}
