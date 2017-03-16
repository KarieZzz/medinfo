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
        DB::statement('CREATE VIEW units_view AS
              SELECT
                  u.id,
                  u.unit_code AS code,
                  u.unit_name AS name,
                  u.node_type AS type
              FROM
                  mo_hierarchy u
              UNION
              SELECT
                  g.id,
                  g.group_code AS code,
                  g.group_name AS name,
                  5 AS type
              FROM
                  unit_groups g
                  ORDER BY code');
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
