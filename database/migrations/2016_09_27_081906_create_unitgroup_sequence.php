<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUnitgroupSequence extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // стартовый номер должен быть согласован с units и unit_groups
        DB::statement("CREATE SEQUENCE public.unit_id_seq
            INCREMENT 1 MINVALUE 1
            MAXVALUE 9223372036854775807 START 1 CACHE 1;
            ALTER SEQUENCE public.unit_id_seq RESTART WITH 604;
            ALTER TABLE mo_hierarchy ALTER COLUMN id SET DEFAULT nextval('unit_id_seq');
            ALTER TABLE unit_groups ALTER COLUMN id SET DEFAULT nextval('unit_id_seq');");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        DB::statement('DROP SEQUENCE public.unit_id_seq');
    }
}
