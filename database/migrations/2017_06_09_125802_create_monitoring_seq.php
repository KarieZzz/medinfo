<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMonitoringSeq extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        DB::statement('ALTER SEQUENCE public.monitorings_id_seq
              INCREMENT 1 MINVALUE 100001 MAXVALUE 9223372036854775807 START 100001
              CACHE 1
              NO CYCLE;;');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
