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
    // Менеджер пользователей - исполнителей
    Route::get('admin/workers', 'Admin\WorkerAdmin@index' );
    Route::get('admin/fetch_workers', 'Admin\WorkerAdmin@fetch_workers');
    Route::get('admin/fetch_mo_tree/{parent}', 'Admin\DocumentAdminController@fetch_mo_hierarchy');
    Route::get('admin/fetch_worker_scopes/{id}', 'Admin\WorkerAdmin@fetch_worker_scopes');
    Route::post('admin/workers/create', 'Admin\WorkerAdmin@worker_store');
    Route::patch('admin/workers/update', 'Admin\WorkerAdmin@worker_update');
    Route::patch('admin/workers/updateuserscope', 'Admin\WorkerAdmin@worker_scope_update');
    // Менеджер организационных единиц
    Route::get('admin/units', 'Admin\MOAdminController@index');
    Route::get('admin/units/fetchunits', 'Admin\MOAdminController@fetchUnits');
    Route::post('admin/units/create', 'Admin\MOAdminController@unitStore');
    Route::patch('admin/units/update/{unit}', 'Admin\MOAdminController@unitUpdate');
    Route::delete('admin/units/delete/{unit}', 'Admin\MOAdminController@unitDelete');

    // Менеджер отчетных периодов
    Route::get('admin/periods', 'Admin\PeriodAdminController@index' );
    Route::get('admin/fetchperiods', 'Admin\PeriodAdminController@fetchPeriods' );
    Route::post('admin/periods/create', 'Admin\PeriodAdminController@store');
    Route::patch('admin/periods/update', 'Admin\PeriodAdminController@update');
    Route::delete('admin/periods/delete/{period}', 'Admin\PeriodAdminController@delete');
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
    // Менеджер отчетных документов
    Route::get('admin/documents', 'Admin\DocumentAdminController@index');
    Route::get('admin/fetchdocuments', 'Admin\DocumentAdminController@fetchDocuments');
    Route::post('admin/createdocuments', 'Admin\DocumentAdminController@createDocuments');
    Route::delete('admin/deletedocuments', 'Admin\DocumentAdminController@deleteDocuments');
    Route::patch('admin/erasedocuments', 'Admin\DocumentAdminController@eraseStatData');
    Route::patch('admin/documentstatechange', 'Admin\DocumentAdminController@changeState');

    // Ввод и корректировка статданных
    // Рабочий стол - Первичные и сводные отчеты, сообщения, проверки и экспорт в эксель
    Route::get('datainput', 'StatDataInput\DocumentDashboardController@index' );
    Route::get('datainput/fetch_mo_tree/{parent}', 'StatDataInput\DocumentDashboardController@fetch_mo_hierarchy');
    Route::get('datainput/fetchdocuments', 'StatDataInput\DocumentDashboardController@fetchdocuments');
    Route::get('datainput/fetchaggregates', 'StatDataInput\DocumentDashboardController@fetchaggregates');
    Route::get('datainput/fetchmessages', 'StatDataInput\DocumentMessageController@fetchMessages');
    Route::get('datainput/fetchauditions', 'StatDataInput\DocumentAuditionController@fetchAuditions');
    Route::post('datainput/sendmessage', 'StatDataInput\DocumentMessageController@sendMessage');
    Route::post('datainput/changestate', 'StatDataInput\DocumentStateController@changeState');
    Route::post('datainput/changeaudition', 'StatDataInput\DocumentAuditionController@changeAudition');
    Route::patch('datainput/aggregatedata/{document}', 'StatDataInput\AggregatesDashboardController@aggregateData' );

    // Рабочий стол - Первичный отчетный документ, ввод данных, контроль, журнал изменений
    Route::get('datainput/formdashboard/{document}', 'StatDataInput\FormDashboardController@index');
    Route::get('datainput/fetchvalues/{document}/{table}', 'StatDataInput\FormDashboardController@fetchValues');
    Route::post('datainput/savevalue/{document}/{table}', 'StatDataInput\FormDashboardController@saveValue');
    Route::get('datainput/valuechangelog/{document}', 'StatDataInput\FormDashboardController@fullValueChangeLog');
    Route::get('datainput/formcontrol/{document}', 'StatDataInput\FormDashboardController@formControl');
    Route::get('datainput/tablecontrol/{document}/{table}', 'StatDataInput\FormDashboardController@tableControl');

    // Эспорт данных в Эксель и заполнение печатных форм-шаблонов
    Route::get('datainput/formexport/{document}', 'StatDataInput\ExcelExportController@formExport');
    Route::get('datainput/tableexport/{document}/{table}', 'StatDataInput\ExcelExportController@dataTableExport');

    // Рабочий стол для сводных документов
    Route::get('datainput/aggregatedashboard/{document}', 'StatDataInput\AggregatesDashboardController@index');
    Route::get('datainput/fetchcelllayers/{document}/{row}/{column}', 'StatDataInput\AggregatesDashboardController@fetchAggregatedCellLayers');

    // Аналитика: консолидированные отчеты, справки
    Route::get('reports/by_mo', 'ReportController@consolidateIndexes');

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
