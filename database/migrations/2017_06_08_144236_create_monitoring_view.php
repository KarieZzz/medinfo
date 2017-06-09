<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMonitoringView extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        DB::statement('CREATE VIEW monitoring_view AS
            SELECT concat(\'m\', m.id, \'_\') AS el,
                m.name,
                NULL::text AS parent
            FROM monitorings m
            UNION
            SELECT concat(\'f\', m.id, \'_\', f.id) AS el,
                concat(\'(\', f.form_code, \') \', f.form_name) AS name,
                concat(\'m\', m.id, \'_\') AS parent
            FROM monitorings m
                 JOIN albums a ON m.album_id = a.id
                 JOIN album_forms af ON a.id = af.album_id
                 JOIN forms f ON af.form_id = f.id;');
    }
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        DB::statement('DROP VIEW monitoring_view');
    }
}
