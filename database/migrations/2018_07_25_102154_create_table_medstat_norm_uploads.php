<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableMedstatNormUploads extends Migration
{
    /**
     * Run the migrations.
     * Таблица для нормальзованной выгрузки данных из Медстат (ЦНИИОИЗ)
     * @return void
     */
    public function up()
    {
        Schema::create('medstat_norm_uploads', function (Blueprint $table) {
            $table->increments('id');//
            $table->char('year', 2);
            $table->char('ucode', 4);
            $table->char('form', 5);
            $table->char('table', 4);
            $table->char('row', 3);
            $table->char('column', 2);
            $table->decimal('value', 12, 2);
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
        Schema::drop('medstat_norm_uploads');
    }
}
