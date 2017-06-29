<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAlbumFormsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('album_forms', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('album_id')->index();
            $table->integer('form_id')->index();
            $table->string('alternate_code', 7)->unique();
            $table->string('alternate_name', 256)->index();
            $table->timestamps();
            $table->unique(['album_id', 'form_id' ]);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('album_forms');
    }
}
