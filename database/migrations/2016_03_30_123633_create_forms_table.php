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
            $table->integer('form_index')->unique();
            $table->string('form_code', 7)->unique();
            $table->string('form_name', 256)->unique();
            $table->char('medstat_code', 5)->nullable()->unique();
            $table->char('short_ms_code', 5)->nullable()->unique();
            $table->integer('relation')->nullable()->index();
            $table->integer('medstatnsk_id')->nullable()->index();
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
