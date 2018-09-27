<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUnitsView extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        DB::statement('CREATE OR REPLACE VIEW public.units_view (
                id,
                code,
                name,
                type)
            AS
            SELECT u.id,
                u.unit_code AS code,
                u.unit_name AS name,
                u.node_type AS type
            FROM mo_hierarchy u
            UNION
            SELECT l.id,
                l.slug AS code,
                l.name,
                100 AS type
            FROM unit_lists l
            ORDER BY 2;');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        DB::statement('DROP VIEW units_view');
    }
}
