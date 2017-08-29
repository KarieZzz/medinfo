@extends('jqxadmin.app')

@section('title', 'Пользователи - администраторы и эксперты')
@section('headertitle', 'Пользователи - администраторы и эксперты')

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
                        <div class="col-sm-4">
                            <input type="text" class="form-control" id="name">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-sm-3" for="email">E-mail:</label>
                        <div class="col-sm-4">
                            <input type="email" class="form-control" id="email">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-sm-3" for="password">Пароль:</label>
                        <div class="col-sm-4">
                            <input type="text" class="form-control" id="password">
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-sm-offset-2 col-sm-7">
                            <button type="button" id="save" class="btn btn-primary">Сохранить изменения</button>
                            <button type="button" id="insert" class="btn btn-success">Вставить новую запись</button>
                            <button type="button" id="delete" class="btn btn-danger">Удалить запись</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('loadjsscripts')
    <script src="{{ asset('/jqwidgets/jqxsplitter.js') }}"></script>
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
    <script src="{{ asset('/jqwidgets/jqxgrid.sort.js') }}"></script>
    <script src="{{ asset('/jqwidgets/jqxgrid.columnsresize.js') }}"></script>
    <script src="{{ asset('/jqwidgets/jqxgrid.selection.js') }}"></script>
    <script src="{{ asset('/jqwidgets/jqxdatatable.js') }}"></script>
    <script src="{{ asset('/medinfo/admin/useradmin.js?v=002') }}"></script>
@endpush

@section('inlinejs')
    @parent
    <script type="text/javascript">
        let selected_scopes = [];
        let ulist = $('#userList');
        let user_url = '/users';
        let workerdelete_url = '/admin/workers/delete/';
        let fetchusers_url = '/admin/fetchusers';
        initsplitter();
        inituserlist();
        initactions();
    </script>
@endsection
