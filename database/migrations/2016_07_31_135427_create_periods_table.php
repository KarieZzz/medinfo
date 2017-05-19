<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePeriodsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('periods', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 64)->unique();
            $table->char('year', 4)->index();
            $table->date('begin_date')->index();
            $table->date('end_date')->index();
            $table->integer('pattern_id')->index();
            $table->char('medinfo_id', 8)->nullable()->index();
            $table->timestamps();
            $table->unique(['year', 'pattern_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('periods');
    }
}
