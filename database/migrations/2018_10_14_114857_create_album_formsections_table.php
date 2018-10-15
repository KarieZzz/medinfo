<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAlbumFormsectionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create('album_formsections', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('album_id')->index();
            $table->integer('formsection_id')->index();
            $table->timestamps();
            $table->unique(['album_id', 'formsection_id' ]);
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
        Schema::drop('album_formsections');
    }
}
