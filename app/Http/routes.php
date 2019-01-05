<?php

/*
|--------------------------------------------------------------------------
| Routes File
|--------------------------------------------------------------------------
|
| Here is where you will register all of the routes in an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| This route group applies the "web" middleware group to every route
| it contains. The "web" middleware group is defined in your HTTP
| kernel and includes session state, CSRF protection, and more.
|
*/

//Route::group(['prefix' => 'admin', 'middleware' => 'auth:api'], function () {
Route::group(['middleware' => 'auth:api'], function () {

});


// Маршруты с авторизацией вынесены за пределы группы web
Route::group(['middleware' => ['medinfo']], function () {
    //Route::auth();
    Route::get('login', 'Auth\AdminAuthController@getLogin' );
    Route::post('login', 'Auth\AdminAuthController@login' );
    Route::get('admin/logout', 'Auth\AdminAuthController@logout');
    Route::get('analyticlogin', 'Auth\AnaliticAuthController@getLogin');
    Route::post('analyticlogin', 'Auth\AnaliticAuthController@login');
    Route::get('analytics/logout', 'Auth\AnaliticAuthController@logout');
    Route::get('workerlogin', 'Auth\DatainputAuthController@getLogin' );
    Route::post('workerlogin', 'Auth\DatainputAuthController@login' );
    Route::get('workerlogout', 'Auth\DatainputAuthController@logout' );

    // Shared Resources
    Route::get('/fetchforms', 'Shared\FormTablePickerController@fecthForms');
    Route::get('/fetchtables/{form}', 'Shared\FormTablePickerController@fetchTables');

    // Маршрут по умолчанию - ввод данных
    Route::get('/', 'StatDataInput\DocumentDashboardController@index' );

    // Шаблоны на основе jQWidgets для администрирования
    Route::get('admin', 'Admin\AdminController@index');
    Route::get('medstat_export/{document}', 'Admin\MedstatExportController@msExport');
    Route::get('medstat_table_export/{document}/{table}', 'Admin\MedstatExportController@tableMedstatExport');

    // Менеджер пользователей - администраторов, экспертов
    Route::get('users', 'Admin\UserAdminController@index');
    Route::get('admin/fetchusers', 'Admin\UserAdminController@fetchUsers');
    Route::post('users', 'Admin\UserAdminController@store');
    Route::patch('users/{user}', 'Admin\UserAdminController@update');
    Route::delete('users/{user}', 'Admin\UserAdminController@destroy');

    // Менеджер пользователей - исполнителей
    Route::get('admin/workers', 'Admin\WorkerAdmin@index' );
    Route::get('admin/fetch_workers', 'Admin\WorkerAdmin@fetch_workers');
    Route::get('admin/workers/fetch_units', 'Admin\WorkerAdmin@fetch_units');
    Route::get('admin/workers/fetch_scopes/{id}', 'Admin\WorkerAdmin@fetch_worker_scopes');
    Route::post('admin/workers/create', 'Admin\WorkerAdmin@worker_store');
    Route::patch('admin/workers/update/{worker}', 'Admin\WorkerAdmin@worker_update');
    Route::patch('admin/workers/updateuserscope', 'Admin\WorkerAdmin@worker_scope_update');
    Route::delete('admin/workers/delete/{worker}', 'Admin\WorkerAdmin@worker_delete');

    // Менеджер организационных единиц
    Route::get('admin/units', 'Admin\MOAdminController@index');
    Route::get('admin/units/fetchunits', 'Admin\MOAdminController@fetchUnits');
    Route::post('admin/units/create', 'Admin\MOAdminController@unitStore');
    Route::patch('admin/units/update/{unit}', 'Admin\MOAdminController@unitUpdate');
    Route::delete('admin/units/delete/{unit}', 'Admin\MOAdminController@unitDelete');

    // Менеджер групп организационных единиц
    Route::get('admin/units/groups', 'Admin\UnitGroupAdminController@index');
    Route::get('admin/units/fetchgroups', 'Admin\UnitGroupAdminController@fetchGroups');
    Route::get('admin/units/fetchgroupnonmembers/{group}', 'Admin\UnitGroupAdminController@fetchNonMembers');
    Route::get('admin/units/fetchmembers/{group}', 'Admin\UnitGroupAdminController@fetchMembers');
    Route::post('admin/units/groupcreate', 'Admin\UnitGroupAdminController@store');
    Route::patch('admin/units/groupupdate/{group}', 'Admin\UnitGroupAdminController@update');
    Route::delete('admin/units/groupdelete/{group}', 'Admin\UnitGroupAdminController@delete');
    Route::post('admin/units/addmembers/{group}', 'Admin\UnitGroupAdminController@addMembers');
    Route::delete('admin/units/removemember/{group}/{members}', 'Admin\UnitGroupAdminController@removeMember');

    // Менеджер отчетных периодов
    Route::get('admin/periods', 'Admin\PeriodAdminController@index' );
    Route::get('admin/fetchperiods', 'Admin\PeriodAdminController@fetchPeriods' );
    Route::post('admin/periods/create', 'Admin\PeriodAdminController@store');
    // Процедура создания нового периода строго по шаблону
    Route::post('admin/periods/store', 'Admin\PeriodAdminController@storeByPattern');
    Route::patch('admin/periods/update', 'Admin\PeriodAdminController@update');
    Route::delete('admin/periods/delete/{period}', 'Admin\PeriodAdminController@delete');

    // Менеджер мониторингов
    Route::get('admin/monitorings/fetchlist', 'Admin\MonitoringController@fetchList');
    Route::resource('admin/monitorings', 'Admin\MonitoringController');

    //Менеджер альбомов форм
    Route::get('admin/albums', 'Admin\AlbumAdminController@index' );
    Route::get('admin/fetchalbums', 'Admin\AlbumAdminController@fetchAlbums' );
    Route::get('admin/albums/fetchformset/{album}', 'Admin\AlbumAdminController@fetchFormSet' );
    Route::post('admin/albums/create', 'Admin\AlbumAdminController@store');
    Route::patch('admin/albums/update/{album}', 'Admin\AlbumAdminController@update');
    Route::delete('admin/albums/delete/{album}', 'Admin\AlbumAdminController@delete');
    Route::post('admin/albums/addmembers/{album}', 'Admin\AlbumAdminController@addMembers');
    Route::delete('admin/albums/removemember/{member}', 'Admin\AlbumAdminController@removeMember');

    // Менеджер отчетных форм/таблиц
    Route::get('admin/forms', 'Admin\FormAdminController@index');
    Route::get('admin/fetchforms', 'Admin\FormAdminController@fetchForms' );
    Route::post('admin/forms/create', 'Admin\FormAdminController@store');
    Route::patch('admin/forms/update/{form}', 'Admin\FormAdminController@update');
    Route::delete('admin/forms/delete/{form}', 'Admin\FormAdminController@delete');
    Route::get('admin/tables', 'Admin\TableAdminController@index');
    Route::get('admin/fetchtables', 'Admin\TableAdminController@fetchTables' );
    Route::post('admin/tables', 'Admin\TableAdminController@store');
    Route::patch('admin/tables/update/{table}', 'Admin\TableAdminController@update');
    Route::delete('admin/tables/delete/{table}', 'Admin\TableAdminController@delete');
    Route::patch('admin/tables/up/{table}', 'Admin\TableAdminController@up');
    Route::patch('admin/tables/down/{table}', 'Admin\TableAdminController@down');
    Route::patch('admin/tables/top/{table}', 'Admin\TableAdminController@top');
    Route::patch('admin/tables/bottom/{table}', 'Admin\TableAdminController@bottom');

    // Менеджер разделов форм
    Route::get('admin/formsections/fetchfs', 'Admin\FormSectionAdminController@fetch_formsections');
    Route::get('admin/formsections/editsection/{fs}', 'Admin\FormSectionAdminController@editSection');
    Route::patch('admin/formsections/editsection/{fs}', 'Admin\FormSectionAdminController@updateSectionSet');
    Route::resource('admin/formsections', 'Admin\FormSectionAdminController');

    // Менеджер строк и граф
    Route::get('admin/rc', 'Admin\RowColumnAdminController@index');
    Route::get('admin/rc/fetchrows/{table}', 'Admin\RowColumnAdminController@fetchRows');
    Route::get('admin/rc/fetchcolumns/{table}', 'Admin\RowColumnAdminController@fetchColumns');
    Route::get('admin/rc/fetchtables/{form}', 'Admin\RowColumnAdminController@fetchTables');
    Route::patch('admin/rc/rowupdate/{row}', 'Admin\RowColumnAdminController@rowUpdate');
    Route::post('admin/rc/rowcreate', 'Admin\RowColumnAdminController@rowStore');
    Route::delete('admin/rc/rowdelete/{row}', 'Admin\RowColumnAdminController@rowDelete');
    Route::patch('admin/rc/columnupdate/{column}', 'Admin\RowColumnAdminController@columnUpdate');
    Route::delete('admin/rc/columndelete/{column}', 'Admin\RowColumnAdminController@columnDelete');
    Route::post('admin/rc/columncreate', 'Admin\RowColumnAdminController@columnStore');
    // Изменение порядка строк
    Route::patch('admin/rc/rowup/{row}', 'Admin\RowColumnAdminController@rowUp');
    Route::patch('admin/rc/rowdown/{row}', 'Admin\RowColumnAdminController@rowDown');
    // Изменение порядка граф
    Route::patch('admin/rc/columnleft/{column}', 'Admin\RowColumnAdminController@columnLeft');
    Route::patch('admin/rc/columnright/{column}', 'Admin\RowColumnAdminController@columnRight');

    Route::get('admin/rc/columnformula/show/{column}', 'Admin\CalculatedColumnAdminController@show');
    Route::post('admin/rc/columnformula/store/{column}', 'Admin\CalculatedColumnAdminController@store');
    Route::patch('admin/rc/columnformula/update/{columnCalculation}', 'Admin\CalculatedColumnAdminController@update');

    Route::get('admin/rc/msmimatching/{formcode}', 'Admin\RowColumnAdminController@rowsMatching'); // Сопоставление строк Медстат и Мединфо
    Route::get('admin/rc/grfmatching/{formcode}', 'Admin\RowColumnAdminController@columnsMatching'); // Сопоставление граф Медстат и Мединфо

    // Менеджер нередактируемых ячеек
    Route::get('admin/necells/list', 'Admin\NECellAdminController@list');
    Route::get('admin/necells/conditions', 'Admin\NECellAdminController@conditions');
    Route::get('admin/necells/fetchconditions', 'Admin\NECellAdminController@fetchConditions');
    Route::get('admin/necells/fetchcellcondition/{range}', 'Admin\NECellAdminController@fetchCellsCondition');
    Route::get('admin/necells/grid/{table}', 'Admin\NECellAdminController@fetchGrid');
    Route::get('admin/necells/fetchnecells/{table}', 'Admin\NECellAdminController@fetchValues');
    Route::patch('admin/necells/changecellstate/{row_column}/{newstate}/{condition}', 'Admin\NECellAdminController@toggleCellState');
    Route::patch('admin/necells/range/{range}/{noedit}/{condition}' , 'Admin\NECellAdminController@toggleCellRange');
    Route::post('admin/necells/conditioncreate', 'Admin\NECellAdminController@store');
    Route::delete('admin/necells/conditiondelete/{condition}', 'Admin\NECellAdminController@delete');
    Route::patch('admin/necells/conditionsave/{condition}', 'Admin\NECellAdminController@update');

    // Менеджер функций контроля
    Route::get('admin/cfunctions', 'Admin\CFunctionAdminController@index');
    Route::get('admin/cfunctions/all', 'Admin\CFunctionAdminController@cfunctionsAll');
    Route::get('admin/cfunctions/fetchcf/{table}', 'Admin\CFunctionAdminController@fetchControlFunctions');
    Route::get('admin/cfunctions/fetchofform/{form}', 'Admin\CFunctionAdminController@fetchCFofForm');
    Route::get('admin/cfunctions/fetchall', 'Admin\CFunctionAdminController@fetchCcfunctionsAll');
    Route::post('admin/cfunctions/create/{table}', 'Admin\CFunctionAdminController@store');
    Route::patch('admin/cfunctions/update/{cfunction}', 'Admin\CFunctionAdminController@update');
    Route::delete('admin/cfunctions/delete/{cfunction}', 'Admin\CFunctionAdminController@delete');
    Route::get('admin/cfunctions/recompiletable/{scopeTable}', 'Admin\CFunctionAdminController@recompileTable');
    Route::get('admin/cfunctions/recompileform/{scopeFrom}', 'Admin\CFunctionAdminController@recompileForm');
    Route::get('admin/dcheck/selected/perform', 'Analytics\SelectedCFunctionCheckController@performControl');
    Route::get('admin/dcheck/selected/getprogress', 'Analytics\SelectedCFunctionCheckController@getProgess');
    Route::get('admin/dcheck/selected/{cf}', 'Analytics\SelectedCFunctionCheckController@index');
    Route::get('admin/cfunctions/excelexport/{form}', 'Admin\CFunctionAdminController@excelExport');

    // Менеджер правил рассчета консолидированных таблиц
    Route::resource('admin/consolidation', 'Admin\ConsolidationRuleAdminController');
    Route::delete('admin/consolidation/{row}/{column}', 'Admin\ConsolidationRuleAdminController@destroy');
    Route::get('admin/consolidation/getstruct/{table}', 'Admin\ConsolidationRuleAdminController@getTableStruct' );
    Route::get('admin/consolidation/getrules/{table}', 'Admin\ConsolidationRuleAdminController@getRules' );
    Route::get('admin/consolidation/getrules_old/{table}', 'Admin\ConsolidationRuleAdminController@getRules_old' );

    Route::get('admin/cons', 'Admin\ConsRulesAndListsAdminController@index');
    Route::patch('admin/cons/applyrule', 'Admin\ConsRulesAndListsAdminController@applyRule');
    Route::delete('admin/cons/applyrule', 'Admin\ConsRulesAndListsAdminController@clearRule');
    Route::patch('admin/cons/applylist', 'Admin\ConsRulesAndListsAdminController@applyList');
    Route::delete('admin/cons/applylist', 'Admin\ConsRulesAndListsAdminController@clearList');
    Route::get('admin/cons/{row}/{column}', 'Admin\ConsRulesAndListsAdminController@getRule');

    // Расчет консолидированных документов
    Route::get('admin/consolidate/{document}', 'Admin\DocumentConsolidationController@consolidateDocument' );
    Route::get('admin/consolidate_table/{document}/{table}', 'Admin\DocumentConsolidationController@consolidatePivotTableByRule' );
    Route::get('admin/cons_by_rule_list/{document}/{table}', 'Admin\DocumentConsolidationController@consolidatePivoteTableByRuleAndUnitlist' );

    // Менеджер списков МО для формирования сводов/рассчета консолидированных таблиц
    Route::resource('admin/units/lists', 'Admin\ListMOAdminController');
    Route::post('admin/units/lists/createcopy/{list}', 'Admin\ListMOAdminController@storeAs');
    Route::get('admin/units/fetchlists', 'Admin\ListMOAdminController@fetchlits');
    Route::get('admin/units/fetchlists_w_reserved', 'Admin\ListMOAdminController@fetchlits_with_reserved');
    Route::get('admin/units/fetchlistmembers/{list}', 'Admin\ListMOAdminController@fetchListMembers');
    Route::get('admin/units/nonmembers/{list}', 'Admin\ListMOAdminController@fetchNonMembers');
    Route::post('admin/units/addlistmembers/{list}', 'Admin\ListMOAdminController@addMembers');
    Route::delete('admin/units/removelistmembers/{list}/{members}', 'Admin\ListMOAdminController@removeMembers');
    Route::delete('admin/units/removeall/{list}', 'Admin\ListMOAdminController@removeAll');
/*    Route::get('admin/micontrols/vtk', 'Admin\MedinfoControlsAdminController@index');
    Route::get('admin/micontrols/fetchcontrolledrows/{table}/{scope}', 'Admin\MedinfoControlsAdminController@fetchControlledRows');
    Route::get('admin/micontrols/vtk/fetchcontrollingrows/{table}/{relation}', 'Admin\MedinfoControlsAdminController@fetchControllingRows');
    Route::get('admin/micontrols/fetchcolumns/{firstcol}/{countcol}', 'Admin\MedinfoControlsAdminController@fetchColumns');
    Route::get('admin/micontrols/translate/{form}', 'Admin\MedinfoControlsAdminController@MIRulesTranslate');
    Route::get('admin/micontrols/saverules', 'Admin\MedinfoControlsAdminController@BatchRuleSave');*/
    // Менеджер отчетных документов
    Route::get('admin/documents', 'Admin\DocumentAdminController@index');
    Route::get('admin/documents/fetchmotree', 'Admin\DocumentAdminController@fetch_mo_hierarchy');
    Route::get('admin/fetchugroups', 'Admin\DocumentAdminController@fetch_unitgroups');
    Route::get('admin/fetch_mon_tree', 'Admin\DocumentAdminController@fetch_monitorings');
    Route::get('admin/fetchdocuments', 'Admin\DocumentAdminController@fetchDocuments');
    Route::post('admin/createdocuments', 'Admin\DocumentAdminController@createDocuments');
    Route::post('admin/clonedocuments', 'Admin\DocumentAdminController@cloneDocumentsToNewPeriod');
    Route::delete('admin/deletedocuments', 'Admin\DocumentAdminController@deleteDocuments');
    Route::patch('admin/erasedocuments', 'Admin\DocumentAdminController@eraseStatData');
    Route::patch('admin/documentstatechange', 'Admin\DocumentAdminController@changeState');
    Route::patch('admin/protectaggregates', 'Admin\DocumentAdminController@protect_aggregated');
    Route::get('admin/documents/valuechanginglog/{document}', 'Admin\ValueChangingAdminController@showFormEditingLog');

    Route::get('admin/documents/create_set1', 'Admin\DocumentAdminController@documentSetCreating1');
    Route::get('admin/documents/create_set2', 'Admin\DocumentAdminController@documentSetCreating2');
    Route::get('admin/documents/create_set3', 'Admin\DocumentAdminController@documentSetCreating3');
    Route::get('admin/documents/create_set4', 'Admin\DocumentAdminController@documentSetCreating4');
    Route::get('admin/documents/create_set5', 'Admin\DocumentAdminController@documentSetCreating5');

    // импорт данных из формата Медстат
    Route::get('admin/documents/medstatimport', 'ImportExport\MedstatImportAdminController@index');
    Route::post('admin/documents/medstatimport', 'ImportExport\MedstatImportAdminController@uploadNormalizedMedstatData');
    Route::post('admin/documents/medstatimportmake', 'ImportExport\MedstatImportAdminController@makeMedstatImport');
    // импорт структуры таблиц из формата Медстат (ЦНИИОИЗ)
    Route::get('admin/sctruct/ms_rows_columns_import', 'ImportExport\ImportMsRowsColumns@index');
    Route::post('admin/sctruct/ms_rows_columns_import', 'ImportExport\ImportMsRowsColumns@uploadMedstatSrtuct');
    // импорт территорий и медициских организаций из формата Медстат (Новосибирск)
    Route::get('admin/units/medstatimport', 'ImportExport\MedstatImportAdminController@selectFileNSMedstatUnits');
    Route::post('admin/units/medstatimport', 'ImportExport\MedstatImportAdminController@uploadFileNSMedstatUnits');
    // импорт данных по соответствию структуры формата Медстат (Новосибирск) и формата Медстат (ЦНИИОИЗ)
    Route::get('admin/sctruct/medstatimport', 'ImportExport\MedstatImportAdminController@selectFileNSMedstatLinks');
    Route::post('admin/sctruct/medstatimport', 'ImportExport\MedstatImportAdminController@uploadFileNSMedstatLinks');
    // Импорт данных из формата Медстат(Новосибирск)
    Route::get('admin/documents/medstatnskimport', 'ImportExport\MedstatNskDataImportController@selectFileNSMedstatData');
    //Route::post('admin/documents/medstatnskimport', 'ImportExport\MedstatNskDataImportController@uploadFileNSMedstatData');
    Route::post('admin/documents/medstatnskimport', 'ImportExport\MedstatNskDataImportController@uploadFileNSMedstatDataCsv');
    Route::post('admin/documents/medstatnskimportmake', 'ImportExport\MedstatNskDataImportController@makeNSMedstatDataImport');
    // Импорт контролей из формата Медстат(Новосибирск)
    Route::get('admin/cfunctions/medstatnskimport', 'ImportExport\MedstatNskControlImportController@selectFileNSMedstatControls');
    Route::post('admin/cfunctions/medstatnskimport', 'ImportExport\MedstatNskControlImportController@uploadFileNSMedstatControlCsv');
    Route::post('admin/cfunctions/medstatnskimportmake', 'ImportExport\MedstatNskControlImportController@makeNSMedstatControlImport');

    // Утилиты для обслуживания системы
    Route::get('admin/system/fixrowindex', 'System\FixRowColumnIndexes@fixRowIndexes');
    Route::get('admin/system/fixcolumnindex', 'System\FixRowColumnIndexes@fixColumnIndexes');
    Route::get('admin/system/fixtableindex', 'System\FixRowColumnIndexes@fixTableIndexes');
    Route::get('admin/system/clearnecells', 'System\ClearNECells@index');
    Route::get('admin/system/setusertokens', 'System\ManageUsers@setTokens');
    Route::post('admin/system/clearnecells', 'System\ClearNECells@clearNECells');
    Route::get('admin/system/messages/markread', 'System\ManageNotifications@markMessagesAsRead');
    Route::get('admin/cfunctions/recompileall', 'Admin\CFunctionAdminController@recompileAll');


    // Ввод и корректировка статданных
    // Рабочий стол - Первичные и сводные отчеты, сообщения, проверки и экспорт в эксель
    Route::get('datainput', 'StatDataInput\DocumentDashboardController@index' );
    Route::get('datainput/fetch_mo_tree/{parent}', 'StatDataInput\DocumentDashboardController@fetch_mo_hierarchy');
    Route::get('datainput/fetch_mon_tree', 'StatDataInput\DocumentDashboardController@fetch_monitorings');
    Route::get('datainput/fetch_ugroups', 'StatDataInput\DocumentDashboardController@fetch_unitgroups');
    Route::get('datainput/fetchdocuments', 'StatDataInput\DocumentDashboardController@fetchdocuments');
    Route::get('datainput/fetchrecent', 'StatDataInput\DocumentDashboardController@fetchRecentDocuments');
    Route::get('datainput/fetchaggregates', 'StatDataInput\DocumentDashboardController@fetchaggregates');
    Route::get('datainput/fetchconsolidates', 'StatDataInput\DocumentDashboardController@fetchconsolidates');
    Route::get('datainput/fetchmessages', 'StatDataInput\DocumentMessageController@fetchMessages');

    Route::get('datainput/fetchauditions', 'StatDataInput\DocumentAuditionController@fetchAuditions');
    Route::get('datainput/fetchdocinfo/{document}', 'StatDataInput\DocInfoController@getDocInfo');
    Route::post('datainput/sendmessage', 'StatDataInput\DocumentMessageController@sendMessage');
    Route::post('datainput/changestate', 'StatDataInput\DocumentStateController@changeState');
    Route::post('datainput/changeaudition', 'StatDataInput\DocumentAuditionController@changeAudition');
    Route::get('datainput/aggregatedata/{document}/{unitgroup}', 'StatDataInput\AggregatesDashboardController@aggregateData' );

    // Рабочий стол - Первичный отчетный документ, ввод данных, журнал изменений
    Route::get('datainput/formdashboard/{document}', 'StatDataInput\FormDashboardController@index');
    Route::get('datainput/fetchvalues/{document}/{album}/{table}', 'StatDataInput\FormDashboardController@fetchValues');
    Route::post('datainput/savevalue/{document}/{table}', 'StatDataInput\FormDashboardController@saveValue');
    Route::post('datainput/excelupload/{document}/{table}/{only}', 'ImportExport\ImportDataFromExcelController@importData');
    Route::get('datainput/valuechangelog/{document}', 'StatDataInput\FormDashboardController@fullValueChangeLog');
    Route::get('datainput/calculate/{document}/{table}', 'StatDataInput\CalculateColumnController@calculate');
    Route::get('datainput/blocksection/{document}/{formsection}/{blocking}', 'StatDataInput\DocumentSectionController@toggleSection');

    // Рабочий стол для сводных/консолидированных документов
    Route::get('datainput/aggregatedashboard/{document}', 'StatDataInput\AggregatesDashboardController@index');
    Route::get('datainput/fetchcelllayers/{document}/{row}/{column}', 'StatDataInput\AggregatesDashboardController@fetchAggregatedCellLayers');
    Route::get('datainput/consolidatedashboard/{document}', 'StatDataInput\ConsolidatesDashboardController@index');
    Route::get('datainput/fetchconsprotocol/{document}/{row}/{column}', 'StatDataInput\ConsolidatesDashboardController@fetchConsolidationProtocol');

    // Контроль данных
    Route::get('datainput/formcontrol/{document}', 'StatDataInput\FormDashboardController@formControl');
    Route::get('datainput/tablecontrol/{document}/{table}', 'StatDataInput\FormDashboardController@tableControl');

    Route::get('datainput/ifdcheck/table/{document}/{table}/{forcereload}', 'StatDataInput\DataCheckController@informTableControl');
    Route::get('datainput/interformdcheck/table/{document}/{table}/{forcereload}', 'StatDataInput\DataCheckController@interFormControl');
    Route::get('datainput/interperioddcheck/table/{document}/{table}/{forcereload}', 'StatDataInput\DataCheckController@interPeriodControl');
    Route::get('datainput/dcheck/table/{document}/{table}/{forcereload}', 'StatDataInput\DataCheckController@check_table');
    Route::get('datainput/dcheck/form/{document}/{forcereload}', 'StatDataInput\DataCheckController@check_document');

    // Эспорт данных в Эксель/Word и заполнение печатных форм-шаблонов
    Route::get('datainput/wordexport/{document}', 'StatDataInput\WordExportController@formExport');
    Route::get('datainput/formexport/{document}', 'StatDataInput\ExcelExportController@dataFormExport');
    Route::get('datainput/tableexport/{document}/{table}', 'StatDataInput\ExcelExportController@dataTableExport');

    // Импорт данных
    Route::get('datainput/excelimport', 'ImportExport\ImportDataFromExcelController@importData');

    // Профиль пользователя
    Route::resource('userprofiles', 'StatDataInput\UserProfileController');
    // Лента сообщений
    Route::get('fetchlatestmessages', 'StatDataInput\DocumentMessageController@fetchRecentMessages');
    Route::post('message/setlastreadtimestamp/{timestamp}', 'StatDataInput\DocumentMessageController@setLastReadTimestamp');
    Route::patch('message/setlastreadtimestamp', 'StatDataInput\DocumentMessageController@markAllAsRead');

    // Аналитика: консолидированные отчеты, справки
    Route::get('reports/br/querycomposer', 'Admin\BriefReferenceController@index');
    Route::get('reports/br/output', 'Admin\BriefReferenceMaker@makeBriefReport');
    Route::get('reports/br/fetchcolumns/{table}', 'Shared\FormTablePickerController@fetchDataTypeColumns');
    Route::get('reports/br/fetchrows/{table}', 'Shared\FormTablePickerController@fetchActualRows');

    Route::get('/reports/patterns', 'Report\ReportPatternController@index');
    Route::get('/reports/patterns/{pattern}/fetchindexes', 'Report\ReportPatternController@showIndexes');
    Route::get('/reports/patterns/create', 'Admin\ReportPatternAdminController@create');
    Route::post('/reports/patterns', 'Admin\ReportPatternAdminController@store');
    Route::get('/reports/patterns/{id}/edit', 'Admin\ReportPatternAdminController@edit');
    Route::patch('/reports/patterns/{pattern}', 'Admin\ReportPatternAdminController@update');

    Route::get('reports/map/{level}/{period}', 'ReportControllerOld@consolidateIndexes');
    Route::get('reports/patterns/{pattern}/{period}/{sortorder}/perform', 'ReportControllerOld@performReport');
    Route::get('/reports/patterns/progress', 'ReportControllerOld@getProgess');

    // Работа с lexer-parser
    Route::get('tests/lexer', 'Tests\LexerParserController@lexerTest');
    Route::get('tests/ast_w_bool', 'Tests\LexerParserController@test_making_AST_w_bool');
    Route::get('tests/parser', 'Tests\LexerParserController@func_parser');
    Route::get('tests/batchRename', 'Tests\LexerParserController@batchRename');
    Route::get('tests/calculation', 'Tests\LexerParserController@testCalculation'); // "Старый" вариант рассчета
    Route::get('tests/calc_mocount', 'Tests\CalculationFunctionTestController@mocount'); // "Новый" вариант рассчета для консолидации
    Route::get('tests/calc_value', 'Tests\CalculationFunctionTestController@calculation'); // "Новый" вариант рассчета для консолидации
    Route::get('tests/calc_valuecount', 'Tests\CalculationFunctionTestController@valuecount'); // "Новый" вариант рассчета для консолидации
    Route::get('tests/vector', 'Tests\VectorTestController@index');
    // тестирование функций контроля
    Route::get('tests/sectioncheck', 'Tests\SectionCheckTestController@SectionCheckTest');
    Route::get('tests/foldcheck', 'Tests\ControlFunctionTestController@fold');
    // mail test
    Route::get('mailtest', 'Tests\MailerTestController@testmail');
    // websocket test
    Route::get('tests/websocket', 'Tests\WebsocketTestController@websocket');

    // Аналитика - отдельный модуль для стастиков и экспертов. Только отчеты, справки, выборочный контроль данных
    Route::get('/analytics', 'Report\ReportController@compose_query');
    Route::get('/analytics/reports', 'Report\ReportController@performReport');

});
