<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableMedstatUploads extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create('medstat_uploads', function (Blueprint $table) {
            $table->increments('id');
            $table->char('A1', 2);
            $table->char('A2', 4);
            $table->char('A4', 7);
            $table->char('A5', 6);
            $table->char('A6', 3);
            $table->decimal('A81', 12, 2);
            $table->decimal('A82', 12, 2);
            $table->decimal('A83', 12, 2);
            $table->decimal('A84', 12, 2);
            $table->decimal('A85', 12, 2);
            $table->decimal('A86', 12, 2);
            $table->decimal('A87', 12, 2);
            $table->decimal('A88', 12, 2);
            $table->decimal('A89', 12, 2);
            $table->decimal('A810', 12, 2);
            $table->decimal('A811', 12, 2);
            $table->decimal('A812', 12, 2);
            $table->decimal('A813', 12, 2);
            $table->decimal('A814', 12, 2);
            $table->decimal('A815', 12, 2);
            $table->decimal('A816', 12, 2);
            $table->decimal('A817', 12, 2);
            $table->decimal('A818', 12, 2);
            $table->decimal('A819', 12, 2);
            $table->decimal('A820', 12, 2);
            $table->decimal('A821', 12, 2);
            $table->decimal('A822', 12, 2);
            $table->decimal('A823', 12, 2);
            $table->decimal('A824', 12, 2);
            $table->decimal('A825', 12, 2);
            $table->decimal('A826', 12, 2);
            $table->decimal('A827', 12, 2);
            $table->decimal('A828', 12, 2);
            $table->decimal('A829', 12, 2);
            $table->decimal('A830', 12, 2);
            $table->decimal('A831', 12, 2);
            $table->decimal('A832', 12, 2);
            $table->decimal('A833', 12, 2);
            $table->decimal('A834', 12, 2);
            $table->decimal('A835', 12, 2);
            $table->decimal('A836', 12, 2);
            $table->decimal('A837', 12, 2);
            $table->decimal('A838', 12, 2);
            $table->decimal('A839', 12, 2);
            $table->decimal('A840', 12, 2);
            $table->decimal('A841', 12, 2);
            $table->decimal('A842', 12, 2);
            $table->decimal('A843', 12, 2);
            $table->decimal('A844', 12, 2);
            $table->decimal('A845', 12, 2);
            $table->decimal('A846', 12, 2);
            $table->decimal('A847', 12, 2);
            $table->decimal('A848', 12, 2);
            $table->decimal('A849', 12, 2);
            $table->decimal('A850', 12, 2);
            $table->char('SRT', 25)->nullable();
            $table->decimal('N1', 2, 0);
            $table->decimal('N2', 2, 0);
            $table->decimal('deleted', 1, 0);
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
        Schema::drop('medstat_uploads');
    }
}
