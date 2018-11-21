<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnUidDocumentMessages extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('document_messages', function (Blueprint $table) {
            $table->uuid('uid')->default(DB::raw('uuid_generate_v4()'))->index();
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
        Schema::table('document_messages', function (Blueprint $table) {
            $table->dropColumn('uid');
        });
    }
}
