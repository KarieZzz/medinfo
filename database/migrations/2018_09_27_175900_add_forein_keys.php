<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddForeinKeys extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
/*        DB::statement('ALTER TABLE public.access_log ADD CONSTRAINT "#accessLog" FOREIGN KEY (user_id) REFERENCES public.workers (id) MATCH SIMPLE ON UPDATE NO ACTION ON DELETE NO ACTION');
        DB::statement('ALTER TABLE public.aggregates ADD CONSTRAINT "#aggregateDocuments" FOREIGN KEY (doc_id) REFERENCES public.documents (id) MATCH SIMPLE ON UPDATE NO ACTION ON DELETE NO ACTION');
        DB::statement('ALTER TABLE public.album_columns ADD CONSTRAINT "#albumColumn" FOREIGN KEY (album_id) REFERENCES public.albums (id) MATCH SIMPLE ON UPDATE NO ACTION ON DELETE NO ACTION');
        DB::statement('ALTER TABLE public.album_forms ADD CONSTRAINT "#forms" FOREIGN KEY (form_id) REFERENCES public.forms (id) MATCH SIMPLE ON UPDATE NO ACTION ON DELETE NO ACTION');
        DB::statement('ALTER TABLE public.album_forms ADD CONSTRAINT "#includedForms" FOREIGN KEY (album_id) REFERENCES public.albums (id) MATCH SIMPLE ON UPDATE NO ACTION ON DELETE NO ACTION;');
        DB::statement('ALTER TABLE public.album_rows ADD CONSTRAINT "#excludedRows" FOREIGN KEY (album_id) REFERENCES public.albums (id) MATCH SIMPLE ON UPDATE NO ACTION ON DELETE NO ACTION');
        DB::statement('ALTER TABLE public.album_tables ADD CONSTRAINT "#albumTabId" FOREIGN KEY (table_id) REFERENCES public.tables (id) MATCH SIMPLE ON UPDATE NO ACTION ON DELETE NO ACTION;');
        DB::statement('ALTER TABLE public.album_tables ADD CONSTRAINT "#tablesAlbumId" FOREIGN KEY (album_id) REFERENCES public.albums (id) MATCH SIMPLE ON UPDATE NO ACTION ON DELETE NO ACTION');
        DB::statement('ALTER TABLE public.cfunctions ADD CONSTRAINT "#cfunctionsTable" FOREIGN KEY (table_id) REFERENCES public.tables (id) MATCH SIMPLE ON UPDATE NO ACTION ON DELETE NO ACTION;');
        DB::statement('ALTER TABLE public.column_calculations ADD CONSTRAINT "#calculation" FOREIGN KEY (column_id) REFERENCES public.columns (id) MATCH SIMPLE ON UPDATE NO ACTION ON DELETE NO ACTION');
        DB::statement('ALTER TABLE public.cons_use_lists ADD CONSTRAINT "#consListRow" FOREIGN KEY (row_id) REFERENCES public.rows (id) MATCH SIMPLE ON UPDATE NO ACTION ON DELETE NO ACTION;');
        DB::statement('ALTER TABLE public.cons_use_rules ADD CONSTRAINT "#consRulesRow" FOREIGN KEY (row_id) REFERENCES public.rows (id) MATCH SIMPLE ON UPDATE NO ACTION ON DELETE NO ACTION');
        DB::statement('ALTER TABLE public.consolidates ADD CONSTRAINT "#consolidateColumn" FOREIGN KEY (column_id) REFERENCES public.columns (id) MATCH SIMPLE ON UPDATE NO ACTION ON DELETE NO ACTION');
        DB::statement('ALTER TABLE public.consolidates ADD CONSTRAINT "#consolidateDocument" FOREIGN KEY (doc_id) REFERENCES public.documents (id) MATCH SIMPLE ON UPDATE NO ACTION ON DELETE NO ACTION');
        DB::statement('ALTER TABLE public.consolidates ADD CONSTRAINT "#consolidateRow" FOREIGN KEY (row_id) REFERENCES public.rows (id) MATCH SIMPLE ON UPDATE NO ACTION ON DELETE NO ACTION;');
        DB::statement('ALTER TABLE public.document_messages ADD CONSTRAINT "#docMesWorker" FOREIGN KEY (user_id) REFERENCES public.workers (id) MATCH SIMPLE ON UPDATE NO ACTION ON DELETE NO ACTION');
        DB::statement('ALTER TABLE public.document_messages ADD CONSTRAINT "#docMessagesDocs" FOREIGN KEY (doc_id) REFERENCES public.documents (id) MATCH SIMPLE ON UPDATE NO ACTION ON DELETE NO ACTION');
        DB::statement('ALTER TABLE public.documents ADD CONSTRAINT "#album" FOREIGN KEY (album_id) REFERENCES public.albums (id) MATCH SIMPLE ON UPDATE NO ACTION ON DELETE NO ACTION');
        DB::statement('ALTER TABLE public.documents ADD CONSTRAINT "#docHierarchy" FOREIGN KEY (ou_id) REFERENCES public.mo_hierarchy (id) MATCH SIMPLE ON UPDATE NO ACTION ON DELETE NO ACTION');
        DB::statement('ALTER TABLE public.documents ADD CONSTRAINT "#form" FOREIGN KEY (form_id) REFERENCES public.forms (id) MATCH SIMPLE ON UPDATE NO ACTION ON DELETE NO ACTION');
        DB::statement('ALTER TABLE public.documents ADD CONSTRAINT "#monitoring" FOREIGN KEY (monitoring_id) REFERENCES public.monitorings (id) MATCH SIMPLE ON UPDATE NO ACTION ON DELETE NO ACTION');
        DB::statement('ALTER TABLE public.documents ADD CONSTRAINT "#period" FOREIGN KEY (period_id) REFERENCES public.periods (id) MATCH SIMPLE ON UPDATE NO ACTION ON DELETE NO ACTION');
        DB::statement('ALTER TABLE public.monitorings ADD CONSTRAINT "#reportAlbum" FOREIGN KEY (album_id) REFERENCES public.albums (id) MATCH SIMPLE ON UPDATE NO ACTION ON DELETE NO ACTION');
        DB::statement('ALTER TABLE public.password_resets ADD CONSTRAINT "#mail" FOREIGN KEY (email) REFERENCES public.users (email) MATCH SIMPLE ON UPDATE NO ACTION ON DELETE NO ACTION');
        DB::statement('ALTER TABLE public.periods ADD CONSTRAINT "#pattern" FOREIGN KEY (pattern_id) REFERENCES public.period_patterns (id) MATCH SIMPLE ON UPDATE NO ACTION ON DELETE NO ACTION');
        DB::statement('ALTER TABLE public.recent_documents ADD CONSTRAINT "#recDocWorker" FOREIGN KEY (worker_id) REFERENCES public.workers (id) MATCH SIMPLE ON UPDATE NO ACTION ON DELETE NO ACTION');
        DB::statement('ALTER TABLE public."rows" ADD CONSTRAINT "#tableRows" FOREIGN KEY (table_id) REFERENCES public.tables (id) MATCH SIMPLE ON UPDATE NO ACTION ON DELETE NO ACTION');
        DB::statement('ALTER TABLE public."columns" ADD CONSTRAINT "#tableColumnss" FOREIGN KEY (table_id) REFERENCES public.tables (id) MATCH SIMPLE ON UPDATE NO ACTION ON DELETE NO ACTION');
        DB::statement('ALTER TABLE public.statdata ADD CONSTRAINT "#statDataColumns" FOREIGN KEY (col_id) REFERENCES public.columns (id) MATCH SIMPLE ON UPDATE NO ACTION ON DELETE NO ACTION');
        DB::statement('ALTER TABLE public.statdata ADD CONSTRAINT "#statDataDoc" FOREIGN KEY (doc_id) REFERENCES public.documents (id) MATCH SIMPLE ON UPDATE NO ACTION ON DELETE NO ACTION');
        DB::statement('ALTER TABLE public.statdata ADD CONSTRAINT "#statDataRow" FOREIGN KEY (row_id) REFERENCES public.rows (id) MATCH SIMPLE ON UPDATE NO ACTION ON DELETE NO ACTION');
        DB::statement('ALTER TABLE public.statdata ADD CONSTRAINT "#statDataTable" FOREIGN KEY (table_id) REFERENCES public.tables (id) MATCH SIMPLE ON UPDATE NO ACTION ON DELETE NO ACTION');
        DB::statement('ALTER TABLE public.table_states ADD CONSTRAINT "#tableStateUser" FOREIGN KEY (user_id) REFERENCES public.users (id) MATCH SIMPLE ON UPDATE NO ACTION ON DELETE NO ACTION');
        DB::statement('ALTER TABLE public.table_states ADD CONSTRAINT "#tableStates" FOREIGN KEY (table_id) REFERENCES public.tables (id) MATCH SIMPLE ON UPDATE NO ACTION ON DELETE NO ACTION');
        DB::statement('ALTER TABLE public.unit_list_members ADD CONSTRAINT "#listMembers" FOREIGN KEY (list_id) REFERENCES public.unit_lists (id) MATCH SIMPLE ON UPDATE NO ACTION ON DELETE NO ACTION');
        DB::statement('ALTER TABLE public.unit_list_members ADD CONSTRAINT "#unitListHierarchy" FOREIGN KEY (ou_id) REFERENCES public.mo_hierarchy (id) MATCH SIMPLE ON UPDATE NO ACTION ON DELETE NO ACTION');
        DB::statement('ALTER TABLE public.valuechanging_log ADD CONSTRAINT "#valueLogWorker" FOREIGN KEY (worker_id) REFERENCES public.workers (id) MATCH SIMPLE ON UPDATE NO ACTION ON DELETE NO ACTION;');
        DB::statement('ALTER TABLE public.worker_scopes ADD CONSTRAINT "#workScopeHierarchy" FOREIGN KEY (ou_id) REFERENCES public.mo_hierarchy (id) MATCH SIMPLE ON UPDATE NO ACTION ON DELETE NO ACTION');
        DB::statement('ALTER TABLE public.worker_scopes ADD CONSTRAINT "#workerScope" FOREIGN KEY (worker_id) REFERENCES public.workers (id) MATCH SIMPLE ON UPDATE NO ACTION ON DELETE NO ACTION');
        DB::statement('ALTER TABLE public.worker_settings ADD CONSTRAINT "#worker" FOREIGN KEY (worker_id) REFERENCES public.workers (id) MATCH SIMPLE ON UPDATE NO ACTION ON DELETE NO ACTION');*/
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
/*        DB::statement('ALTER TABLE public.access_log DROP CONSTRAINT IF EXISTS "#accessLog"');
        DB::statement('ALTER TABLE public.aggregates DROP CONSTRAINT IF EXISTS "#aggregateDocuments"');
        DB::statement('ALTER TABLE public.album_columns DROP CONSTRAINT IF EXISTS "#albumColumn"');
        DB::statement('ALTER TABLE public.album_forms DROP CONSTRAINT IF EXISTS "#forms"');
        DB::statement('ALTER TABLE public.album_forms DROP CONSTRAINT IF EXISTS "#includedForms"');
        DB::statement('ALTER TABLE public.album_rows DROP CONSTRAINT IF EXISTS "#excludedRows"');
        DB::statement('ALTER TABLE public.album_tables DROP CONSTRAINT IF EXISTS "#albumTabId"');
        DB::statement('ALTER TABLE public.album_tables DROP CONSTRAINT IF EXISTS "#tablesAlbumId"');
        DB::statement('ALTER TABLE public.cfunctions DROP CONSTRAINT IF EXISTS "#cfunctionsTable"');
        DB::statement('ALTER TABLE public.column_calculations DROP CONSTRAINT IF EXISTS "#calculation"');
        DB::statement('ALTER TABLE public.cons_use_lists DROP CONSTRAINT IF EXISTS "#consListRow"');
        DB::statement('ALTER TABLE public.cons_use_rules DROP CONSTRAINT IF EXISTS "#consRulesRow"');
        DB::statement('ALTER TABLE public.consolidates DROP CONSTRAINT IF EXISTS "#consolidateColumn"');
        DB::statement('ALTER TABLE public.consolidates DROP CONSTRAINT IF EXISTS "#consolidateDocument"');
        DB::statement('ALTER TABLE public.consolidates DROP CONSTRAINT IF EXISTS "#consolidateRow"');
        DB::statement('ALTER TABLE public.document_messages DROP CONSTRAINT IF EXISTS "#docMesWorker"');
        DB::statement('ALTER TABLE public.document_messages DROP CONSTRAINT IF EXISTS "#docMessagesDocs"');
        DB::statement('ALTER TABLE public.documents DROP CONSTRAINT IF EXISTS "#album"');
        DB::statement('ALTER TABLE public.documents DROP CONSTRAINT IF EXISTS "#docHierarchy"');
        DB::statement('ALTER TABLE public.documents DROP CONSTRAINT IF EXISTS "#form"');
        DB::statement('ALTER TABLE public.documents DROP CONSTRAINT IF EXISTS "#monitoring"');
        DB::statement('ALTER TABLE public.documents DROP CONSTRAINT IF EXISTS "#period"');
        DB::statement('ALTER TABLE public.monitorings DROP CONSTRAINT IF EXISTS "#reportAlbum"');
        DB::statement('ALTER TABLE public.password_resets DROP CONSTRAINT IF EXISTS "#mail"');
        DB::statement('ALTER TABLE public.periods DROP CONSTRAINT IF EXISTS "#pattern"');
        DB::statement('ALTER TABLE public.recent_documents DROP CONSTRAINT IF EXISTS "#recDocWorker"');
        DB::statement('ALTER TABLE public."rows" DROP CONSTRAINT IF EXISTS "#tableRows"');
        DB::statement('ALTER TABLE public."columns" DROP CONSTRAINT IF EXISTS "#tableColumnss"');
        DB::statement('ALTER TABLE public.statdata DROP CONSTRAINT IF EXISTS "#statDataColumns"');
        DB::statement('ALTER TABLE public.statdata DROP CONSTRAINT IF EXISTS "#statDataDoc"');
        DB::statement('ALTER TABLE public.statdata DROP CONSTRAINT IF EXISTS "#statDataRow"');
        DB::statement('ALTER TABLE public.statdata DROP CONSTRAINT IF EXISTS "#statDataTable"');
        DB::statement('ALTER TABLE public.table_states DROP CONSTRAINT IF EXISTS "#tableStateUser"');
        DB::statement('ALTER TABLE public.table_states DROP CONSTRAINT IF EXISTS "#tableStates"');
        DB::statement('ALTER TABLE public.unit_list_members DROP CONSTRAINT IF EXISTS "#listMembers"');
        DB::statement('ALTER TABLE public.unit_list_members DROP CONSTRAINT IF EXISTS "#unitListHierarchy"');
        DB::statement('ALTER TABLE public.valuechanging_log DROP CONSTRAINT IF EXISTS "#valueLogWorker"');
        DB::statement('ALTER TABLE public.worker_scopes DROP CONSTRAINT IF EXISTS "#workScopeHierarchy"');
        DB::statement('ALTER TABLE public.worker_scopes DROP CONSTRAINT IF EXISTS "#workerScope"');
        DB::statement('ALTER TABLE public.worker_settings DROP CONSTRAINT IF EXISTS "#worker"');*/
    }
}
