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
// Маршруты с авторизацией вынесены за пределы группы web


Route::group(['middleware' => ['web']], function () {
    Route::auth();
    Route::get('admin/logout', 'Auth\AuthController@logout');
    Route::get('workerlogin', 'Auth\DatainputAuthController@getLogin' );
    Route::get('workerlogout', 'Auth\DatainputAuthController@logout' );
    Route::post('workerlogin', 'Auth\DatainputAuthController@login' );

    // Маршрут по умолчанию - ввод данных
    Route::get('/', 'StatDataInput\DocumentDashboardController@index' );

    // Шаблоны на основе jQWidgets для администрирования
    Route::get('admin', 'Admin\AdminController@index');
    Route::get('test_export', 'Admin\MedstatExportController@test_export');

    // Менеджер пользователей - исполнителей
    Route::get('admin/workers', 'Admin\WorkerAdmin@index' );
    Route::get('admin/fetch_workers', 'Admin\WorkerAdmin@fetch_workers');
    Route::get('admin/fetch_mo_tree/{parent}', 'Admin\DocumentAdminController@fetch_mo_hierarchy');
    Route::get('admin/fetch_worker_scopes/{id}', 'Admin\WorkerAdmin@fetch_worker_scopes');
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
    Route::get('admin/units/fetchmembers/{group}', 'Admin\UnitGroupAdminController@fetchMembers');
    Route::post('admin/units/groupcreate', 'Admin\UnitGroupAdminController@store');
    Route::patch('admin/units/groupupdate/{group}', 'Admin\UnitGroupAdminController@update');
    Route::delete('admin/units/groupdelete/{group}', 'Admin\UnitGroupAdminController@delete');
    Route::post('admin/units/addmembers/{group}', 'Admin\UnitGroupAdminController@addMembers');
    Route::delete('admin/units/removemember/{member}', 'Admin\UnitGroupAdminController@removeMember');

    // Менеджер отчетных периодов
    Route::get('admin/periods', 'Admin\PeriodAdminController@index' );
    Route::get('admin/fetchperiods', 'Admin\PeriodAdminController@fetchPeriods' );
    Route::post('admin/periods/create', 'Admin\PeriodAdminController@store');
    Route::patch('admin/periods/update', 'Admin\PeriodAdminController@update');
    Route::delete('admin/periods/delete/{period}', 'Admin\PeriodAdminController@delete');

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
    Route::patch('admin/forms/update', 'Admin\FormAdminController@update');
    Route::delete('admin/forms/delete/{form}', 'Admin\FormAdminController@delete');
    Route::get('admin/tables', 'Admin\TableAdminController@index');
    Route::get('admin/fetchtables', 'Admin\TableAdminController@fetchTables' );
    Route::post('admin/tables/create', 'Admin\TableAdminController@store');
    Route::patch('admin/tables/update', 'Admin\TableAdminController@update');
    Route::delete('admin/tables/delete/{table}', 'Admin\TableAdminController@delete');

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
    Route::get('admin/rc/msmimatching/{formcode}', 'Admin\RowColumnAdminController@rowsMatching'); // Сопоставление Медстат и Мединфо

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
    Route::get('admin/cfunctions/fetchcf/{table}', 'Admin\CFunctionAdminController@fetchControlFunctions');
    Route::post('admin/cfunctions/create/{table}', 'Admin\CFunctionAdminController@store');
    Route::patch('admin/cfunctions/update/{cfunction}', 'Admin\CFunctionAdminController@update');
    Route::delete('admin/cfunctions/delete/{cfunction}', 'Admin\CFunctionAdminController@delete');

    Route::get('admin/micontrols/vtk', 'Admin\MedinfoControlsAdminController@index');
    Route::get('admin/micontrols/fetchcontrolledrows/{table}/{scope}', 'Admin\MedinfoControlsAdminController@fetchControlledRows');
    Route::get('admin/micontrols/vtk/fetchcontrollingrows/{table}/{relation}', 'Admin\MedinfoControlsAdminController@fetchControllingRows');
    Route::get('admin/micontrols/fetchcolumns/{firstcol}/{countcol}', 'Admin\MedinfoControlsAdminController@fetchColumns');
    Route::get('admin/micontrols/translate/{form}', 'Admin\MedinfoControlsAdminController@MIRulesTranslate');
    Route::get('admin/micontrols/saverules', 'Admin\MedinfoControlsAdminController@BatchRuleSave');

    // Менеджер отчетных документов
    Route::get('admin/documents', 'Admin\DocumentAdminController@index');
    Route::get('admin/fetchugroups', 'Admin\DocumentAdminController@fetch_unitgroups');
    Route::get('admin/fetchdocuments', 'Admin\DocumentAdminController@fetchDocuments');
    Route::post('admin/createdocuments', 'Admin\DocumentAdminController@createDocuments');
    Route::delete('admin/deletedocuments', 'Admin\DocumentAdminController@deleteDocuments');
    Route::patch('admin/erasedocuments', 'Admin\DocumentAdminController@eraseStatData');
    Route::patch('admin/documentstatechange', 'Admin\DocumentAdminController@changeState');
    Route::patch('admin/protectaggregates', 'Admin\DocumentAdminController@protect_aggregated');

    // Ввод и корректировка статданных
    // Рабочий стол - Первичные и сводные отчеты, сообщения, проверки и экспорт в эксель
    Route::get('datainput', 'StatDataInput\DocumentDashboardController@index' );
    Route::get('datainput/fetch_mo_tree/{parent}', 'StatDataInput\DocumentDashboardController@fetch_mo_hierarchy');
    Route::get('datainput/fetch_ugroups', 'StatDataInput\DocumentDashboardController@fetch_unitgroups');
    Route::get('datainput/fetchdocuments', 'StatDataInput\DocumentDashboardController@fetchdocuments');
    Route::get('datainput/fetchaggregates', 'StatDataInput\DocumentDashboardController@fetchaggregates');
    Route::get('datainput/fetchmessages', 'StatDataInput\DocumentMessageController@fetchMessages');
    Route::get('datainput/fetchauditions', 'StatDataInput\DocumentAuditionController@fetchAuditions');
    Route::post('datainput/sendmessage', 'StatDataInput\DocumentMessageController@sendMessage');
    Route::post('datainput/changestate', 'StatDataInput\DocumentStateController@changeState');
    Route::post('datainput/changeaudition', 'StatDataInput\DocumentAuditionController@changeAudition');
    Route::patch('datainput/aggregatedata/{document}/{unitgroup}', 'StatDataInput\AggregatesDashboardController@aggregateData' );

    // Рабочий стол - Первичный отчетный документ, ввод данных, журнал изменений
    Route::get('datainput/formdashboard/{document}', 'StatDataInput\FormDashboardController@index');
    Route::get('datainput/fetchvalues/{document}/{album}/{table}', 'StatDataInput\FormDashboardController@fetchValues');
    Route::post('datainput/savevalue/{document}/{table}', 'StatDataInput\FormDashboardController@saveValue');
    Route::get('datainput/valuechangelog/{document}', 'StatDataInput\FormDashboardController@fullValueChangeLog');

    // Контроль данных
    Route::get('datainput/formcontrol/{document}', 'StatDataInput\FormDashboardController@formControl');
    Route::get('datainput/tablecontrol/{document}/{table}', 'StatDataInput\FormDashboardController@tableControl');

    Route::get('datainput/dcheck/table/{document}/{table}', 'StatDataInput\DataCheckController@check_table');
    Route::get('datainput/dcheck/form/{document}', 'StatDataInput\DataCheckController@check_document');

    // Эспорт данных в Эксель и заполнение печатных форм-шаблонов
    Route::get('datainput/formexport/{document}', 'StatDataInput\ExcelExportController@formExport');
    Route::get('datainput/tableexport/{document}/{table}', 'StatDataInput\ExcelExportController@dataTableExport');

    // Рабочий стол для сводных документов
    Route::get('datainput/aggregatedashboard/{document}', 'StatDataInput\AggregatesDashboardController@index');
    Route::get('datainput/fetchcelllayers/{document}/{row}/{column}', 'StatDataInput\AggregatesDashboardController@fetchAggregatedCellLayers');

    // Аналитика: консолидированные отчеты, справки
    Route::get('reports/by_mo', 'ReportController@consolidateIndexes');

    // Работа с lexer-parser
    Route::get('lexer/test_parser', 'StatDataInput\DataCheckController@func_parser');
    Route::get('lexer/test_celllexer', 'StatDataInput\DataCheckController@test_celllexer');
    Route::get('lexer/test_cellparser', 'StatDataInput\DataCheckController@test_cellparser');

    // mail test
    Route::get('mailtest', 'StatDataInput\DocumentMessageController@testmail');
});

// Эксперименты с шаблоном AdminLTE
/*Route::get('adminlte', function () {
    return view('welcome');
});
Route::get('adminlte/users', function () {
    return view('users');
});
Route::get('adminlte/logs/accesslog', 'AccessLogController@index');
Route::get('adminlte/logs/accesslog/{event}', 'AccessLogController@show');
// Структура отчетов
// Формы
Route::get('adminlte/structure/forms', 'StructureFormController@index');
Route::get('adminlte/structure/editform/{form}', 'StructureFormController@edit');
Route::patch('adminlte/structure/updateform/{form}', 'StructureFormController@update');
Route::get('adminlte/structure/newform', 'StructureFormController@newform');
Route::post('adminlte/structure/newform', 'StructureFormController@store');
Route::get('adminlte/structure/testquery', 'StructureFormController@testQuery');
// Строки
Route::get('adminlte/structure/rows', 'StructureRowController@showrows');
Route::get('adminlte/structure/editrow/{row}', 'StructureRowController@editrow');
Route::patch('adminlte/structure/updaterow/{row}', 'StructureRowController@updaterow');*/
