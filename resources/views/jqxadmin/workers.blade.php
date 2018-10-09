@extends('jqxadmin.app')

@section('title', 'Пользователи-исполнители отчетов')
@section('headertitle', 'Пользователи-исполнители отчетов')

@section('content')
<div id="mainSplitter" >
    <div>
        <div id="userList" style="margin: 10px"></div>
    </div>
    <div id="formContainer">
        <div id="PropertiesForm" class="panel panel-default">
            <div class="panel-heading"><h3>Данные пользователя</h3></div>
            <div class="panel-body">
                <form id="form" class="form-horizontal">
                    <div class="form-group">
                        <label class="control-label col-sm-3" for="name">Имя пользователя:</label>
                        <div class="col-sm-3">
                            <input type="text" class="form-control" id="name">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-sm-3" for="password">Пароль:</label>
                        <div class="col-sm-3">
                            <input type="text" class="form-control" id="password">
                        </div>
                        <div class="col-sm-3">
                            <button class="btn btn-default" id="pvdGen" type="button">Генерировать</button>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-sm-3" for="description">Описание:</label>
                        <div class="col-sm-8">
                            <textarea rows="2" class="form-control" id="description"></textarea>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-sm-3" for="email">E-mail:</label>
                        <div class="col-sm-4">
                            <input type="email" class="form-control" id="email">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-sm-3" for="role">Роль:</label>
                        <div class="col-sm-4">
                            <select class="form-control" id="role">
                                <option value="1" selected="selected">Исполнитель</option>
                                <option value="2" selected="selected">Эксперт-специалист</option>
                                <option value="3" selected="selected">Эксперт-статистик</option>
                                <option value="4" selected="selected">Руководитель приема отчетов</option>
                                <option value="0" selected="selected">Администратор</option>
                            </select>
                            {{--<input type="number" class="form-control" id="role">--}}
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-sm-3" for="permission">Разрешения:</label>
                        <div class="col-sm-2">
                            <input type="number" class="form-control" id="permission" disabled="disabled">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-sm-3" for="blocked">Блокирован:</label>
                        <div class="col-sm-2">
                            <div id="blocked"></div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-sm-offset-1 col-sm-11">
                            <button type="button" id="save" class="btn btn-primary">Сохранить изменения</button>
                            <button type="button" id="insert" class="btn btn-success">Вставить новую запись</button>
                            <button type="button" id="delete" class="btn btn-danger">Удалить запись</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <div class="well">
            <span><strong>МО/территория, к данным которой имеет доступ пользователь: </strong></span>
            <span id="mo_tree_comment"></span>
        </div>
        <div id="mo_selected_info" style="display: none">
            <span id="mo_selected_name" class='text-info'></span>
            <button type="button" id="mo_selected_save" class="btn btn-default">Сохранить выбор</button>
        </div>
        <div id="mo_tree_container" style="margin: 5px; padding: 10px 0 0 10px">
            <div id="moTree"></div>
        </div>
    </div>
</div>
@endsection

@push('loadjsscripts')
{{--    <script src="{{ asset('/jqwidgets/jqxsplitter.js') }}"></script>
    <script src="{{ asset('/jqwidgets/jqxdata.js') }}"></script>
    <script src="{{ asset('/jqwidgets/jqxpanel.js') }}"></script>
    <script src="{{ asset('/jqwidgets/jqxscrollbar.js') }}"></script>
    <script src="{{ asset('/jqwidgets/jqxinput.js') }}"></script>
    <script src="{{ asset('/jqwidgets/jqxbuttons.js') }}"></script>
    <script src="{{ asset('/jqwidgets/jqxdropdownbutton.js') }}"></script>
    <script src="{{ asset('/jqwidgets/jqxswitchbutton.js') }}"></script>
    <script src="{{ asset('/jqwidgets/jqxcheckbox.js') }}"></script>
    <script src="{{ asset('/jqwidgets/jqxlistbox.js') }}"></script>
    <script src="{{ asset('/jqwidgets/jqxdropdownlist.js') }}"></script>
    <script src="{{ asset('/jqwidgets/jqxgrid.js') }}"></script>
    <script src="{{ asset('/jqwidgets/jqxgrid.filter.js') }}"></script>
    <script src="{{ asset('/jqwidgets/jqxgrid.columnsresize.js') }}"></script>
    <script src="{{ asset('/jqwidgets/jqxgrid.selection.js') }}"></script>
    <script src="{{ asset('/jqwidgets/jqxdatatable.js') }}"></script>
    <script src="{{ asset('/jqwidgets/jqxtreegrid.js') }}"></script>--}}
    <script src="{{ asset('/plugins/pgenerator/jquery.pGenerator.js') }}"></script>
    <script src="{{ asset('/medinfo/admin/workeradmin.js?v=002') }}"></script>
@endpush

@section('inlinejs')
    @parent
    <script type="text/javascript">
        let selected_scopes = [];
        let mo_tree_message = $("#mo_tree_comment");
        let workerupdate_url = '/admin/workers/update/';
        let workerdelete_url = '/admin/workers/delete/';
        let wlist = $("#userList");
        initsplitter();
        inituserlist();
        initmotree();
        initactions();
    </script>
@endsection
