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

Route::group(['middleware' => ['web']], function () {
    Route::auth();
    Route::get('/', function () {
        return view('welcome');
    });

    Route::get('users', function () {
        return view('users');
    });

    Route::get('logs/accesslog', 'AccessLogController@index');
    Route::get('logs/accesslog/{event}', 'AccessLogController@show');

    // Структура отчетов
    // Формы
    Route::get('structure/forms', 'StructureFormController@index');
    Route::get('structure/editform/{form}', 'StructureFormController@edit');
    Route::patch('structure/updateform/{form}', 'StructureFormController@update');
    Route::get('structure/newform', 'StructureFormController@newform');
    Route::post('structure/newform', 'StructureFormController@store');

    Route::get('structure/testquery', 'StructureFormController@testQuery');

    // Строки
    Route::get('structure/rows', 'StructureRowController@showrows');
    Route::get('structure/editrow/{row}', 'StructureRowController@editrow');
    Route::patch('structure/updaterow/{row}', 'StructureRowController@updaterow');

    // Шаблоны на осноые jQWidgets для администрирования
    Route::get('admin', function () {
        return view('jqxadmin.home');
    });
    Route::get('admin/workers', 'Admin\WorkerAdmin@index' );
    Route::get('admin/fetch_workers', 'Admin\WorkerAdmin@fetch_workers');
    Route::get('admin/fetch_mo_tree/{parent}', 'Admin\MOAdminController@fetch_mo_hierarchy');
    //Route::get('admin/fetch_mo_tree', 'Admin\WorkerAdmin@fetch_mo_hierarchy');
    Route::get('admin/fetch_worker_scopes/{id}', 'Admin\WorkerAdmin@fetch_worker_scopes');
    Route::post('admin/workers/create', 'Admin\WorkerAdmin@worker_store');
    Route::patch('admin/workers/update', 'Admin\WorkerAdmin@worker_update');
    Route::patch('admin/workers/updateuserscope', 'Admin\WorkerAdmin@worker_scope_update');

    // Ввод и корректировка статданных
    Route::get('workerlogin', 'Auth\DatainputAuthController@getLogin' );
    Route::get('workerlogout', 'Auth\DatainputAuthController@logout' );
    Route::post('workerlogin', 'Auth\DatainputAuthController@login' );
    // Рабочий стол - Первичные и сводные отчеты, сообщения, проверки и экспорт в эксель
    Route::get('datainput', 'StatDataInput@index' );
    Route::get('datainput/fetchdocuments', 'StatDataInput@fetchdocuments');
    Route::get('datainput/fetchaggregates', 'StatDataInput@fetchaggregates');
    Route::get('datainput/fetchmessages', 'StatDataInput\DocumentMessageController@fetchMessages');
    Route::get('datainput/fetchauditions', 'StatDataInput\DocumentAuditionController@fetchAuditions');
    Route::post('datainput/sendmessage', 'StatDataInput\DocumentMessageController@sendMessage');
    Route::post('datainput/changestate', 'StatDataInput\DocumentStateController@changeState');
    Route::post('datainput/changeaudition', 'StatDataInput\DocumentAuditionController@changeAudition');
    // Рабочий стол - Первичный отчетный документ, ввод данных, контроль, журнал изменений
    Route::get('datainput/formdashboard/{id}', 'StatDataInput\FormDashboardController@index');
    Route::get('datainput/fetchvalues/{document}/{table}', 'StatDataInput\FormDashboardController@fetchValues');
    Route::post('datainput/savevalue/{document}/{table}', 'StatDataInput\FormDashboardController@saveValue');
    Route::get('datainput/formtest/{id}', 'StatDataInput\FormDashboardController@formtest');

});
