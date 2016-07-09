@extends('jqxadmin.app')

@section('title', '<h2>Администратор пользователей - исполнителей отчетов</h2>')

@section('content')
<div id="mainSplitter" >
    <div>
        <div id="userList"></div>
    </div>
    <div id="formContainer" style="width: 100%; height: 100%; overflow: hidden">
        <div id="message" class="alert alert-dismissable"></div>
        <div id="userPropertiesForm" class="box">
            <div class="box box-header">Данные пользователя</div>
            <form id="Form">
                <table style="margin-top: 10px; margin-bottom: 10px; width: 100%;">
                    <tr><td style="text-align:right;">Имя пользователя:</td><td style="text-align:left;"><input type="text" id="name" /></td></tr>
                    <tr><td style="text-align:right;">Пароль:</td><td style="text-align:left;"><input type="text" id="pwd" /></td></tr>
                    <tr><td style="text-align:right;">Описание:</td><td style="text-align:left;"><input type="text" id="description" /></td></tr>
                    <tr><td style="text-align:right;">E-mail:</td><td style="text-align:left;"><input type="text" id="email" /></td></tr>
                    <tr><td style="text-align:right;">Роль:</td><td style="text-align:left;"><input type="text" id="role" /></td></tr>
                    <tr><td style="text-align:right;">Разрешения:</td><td style="text-align:left;"><input type="text" id="permission" /></td></tr>
                    <tr><td style="text-align:right;">Блокирован:</td><td style="text-align:left;"><input type="text" id="blocked" /></td></tr>
                    <tr><td></td><td style="padding-left: 35px; text-align: left;"></td></tr>
                </table>
                <div class="box box-footer">
                    <input value="Сохранить изменения" type="button" id="save" /> или
                    <input value="Вставить новую запись" type="button" id="insert" />
                </div>
            </form>
        </div>
        <div id="mo_tree_comment"></div>
        <div id="mo_tree_container">
            <div id="moTree"></div>
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
    <script src="{{ asset('/jqwidgets/jqxcheckbox.js') }}"></script>
    <script src="{{ asset('/jqwidgets/jqxlistbox.js') }}"></script>
    <script src="{{ asset('/jqwidgets/jqxdropdownlist.js') }}"></script>
    <script src="{{ asset('/jqwidgets/jqxgrid.js') }}"></script>
    <script src="{{ asset('/jqwidgets/jqxgrid.filter.js') }}"></script>
    <script src="{{ asset('/jqwidgets/jqxgrid.columnsresize.js') }}"></script>
    <script src="{{ asset('/jqwidgets/jqxgrid.selection.js') }}"></script>
    <script src="{{ asset('/jqwidgets/jqxdatatable.js') }}"></script>
    <script src="{{ asset('/jqwidgets/jqxtreegrid.js') }}"></script>
@endpush

@section('inlinejs')
    @parent

    <script type="text/javascript">
        var selected_scopes = new Array();
        var message = $("#message");
        $("#mainSplitter").jqxSplitter(
            {
                width: '100%',
                height: '100%',
                theme: theme,
                panels:
                [
                    { size: '60%', min: '10%'},
                    { size: '40%', min: '10%'}
                ]
            }
        );
        var usersource =
        {
            datatype: "json",
            datafields: [
                { name: 'id', type: 'int' },
                { name: 'name', type: 'string' },
                { name: 'pwd', type: 'string' },
                { name: 'email', type: 'string' },
                { name: 'description', type: 'string' },
                { name: 'role', type: 'string' },
                { name: 'permission', type: 'string' },
                { name: 'blocked', type: 'string' }
            ],
            id: 'id',
            url: 'fetch_workers',
            root: null
        };
        var dataAdapter = new $.jqx.dataAdapter(usersource);
        $("#userList").jqxGrid(
                {
                    width: '100%',
                    height: '100%',
                    theme: theme,
                    source: dataAdapter,
                    columnsresize: true,
                    showfilterrow: true,
                    filterable: true,
                    columns: [
                        { text: 'Id', datafield: 'id', width: '30px' },
                        { text: 'Имя', datafield: 'name' , width: '100px'},
                        { text: 'Пароль', datafield: 'pwd', width: '100px' },
                        { text: 'E-mail', datafield: 'email', width: '100px' },
                        { text: 'Описание', datafield: 'description', width: '480px'  },
                        { text: 'Роль', datafield: 'role' },
                        { text: 'Разрешения', datafield: 'permission' },
                        { text: 'Заблокирован', datafield: 'blocked' }
                    ]
                });
        $("#name").jqxInput({height: 23, theme: theme });
        $("#pwd").jqxInput({ height: 23, theme: theme });
        $("#email").jqxInput({ height: 23, theme: theme });
        $("#description").jqxInput({ width: 480, height: 23, theme: theme });
        $("#role").jqxInput({ height: 23, theme: theme });
        $("#permission").jqxInput({ height: 23, theme: theme });
        $("#blocked").jqxInput({ height: 23, theme: theme });
        $("#insert").jqxButton({  width: 180, height: 23, theme: theme });
        $("#save").jqxButton({  width: 180, height: 23, theme: theme });

        var mo_source =
        {
            dataType: "json",
            dataFields: [
                { name: 'id', type: 'int' },
                { name: 'parent_id', type: 'int' },
                { name: 'unit_code', type: 'string' },
                { name: 'unit_name', type: 'string' }
            ],
            hierarchy:
            {
                keyDataField: { name: 'id' },
                parentDataField: { name: 'parent_id' }
            },
            id: 'id',
            root: '',
            url: 'fetch_mo_tree'
        };
        var mo_dataAdapter = new $.jqx.dataAdapter(mo_source);
        $("#moTree").jqxTreeGrid(
                {
                    width: 660,
                    height: 550,
                    source: mo_dataAdapter,
                    selectionMode: "singleRow",
                    filterable: true,
                    filterMode: "simple",
                    columnsResize: true,
                    ready: function()
                    {
                        // expand row with 'EmployeeKey = 32'
                        $("#moTree").jqxTreeGrid('expandRow', 0);
                    },
                    columns: [
                        { text: 'Код', dataField: 'unit_code', width: 100 },
                        { text: 'Наименование', dataField: 'unit_name', width: 540 }
                    ]
                });

        $('#userList').on('rowselect', function (event) {
            message.html('');
            message.removeClass("alert-danger alert-success");
            var row = event.args.row;
            $("#name").val(row.name);
            $("#pwd").val(row.pwd);
            $("#email").val(row.email);
            $("#description").val(row.description);
            $("#role").val(row.role);
            $("#permission").val(row.permission);
            $("#blocked").val(row.blocked);
            $("#scopes").html("");
            //$("#moTree").jqxTree('uncheckAll');
            $("#moTree").jqxTreeGrid('collapseAll');
            $("#moTree").jqxTreeGrid('expandRow', 0);
            var user_scope_url = "fetch_worker_scopes/" + row.id;
            $.getJSON( user_scope_url, function( data) {
                var m = '';
                var mo_tree_comment = data.responce.comment;
                var items = [];
                ou_id = data.responce.scope.ou_id;
                if (ou_id === 0) {
                    m += "Не указаны учреждения к которым имеет доступ пользователь.";
                }
                else {
                    row = $("#moTree").jqxTreeGrid('getRow', ou_id);
                    console.log(row.parent);
                    $("#moTree").jqxTreeGrid('selectRow', ou_id);
                    $("#moTree").jqxTreeGrid('expandRow', row.parent.id);
                    $("#moTree").jqxTreeGrid('expandRow', row.parent.parent.id);
                    $("#moTree").jqxTreeGrid('expandRow', row.parent.parent.parent.id);
                    //$("#moTree").jqxTreeGrid('ensureRowVisible', ou_id);
                    $("#moTree").jqxTreeGrid('scrollOffset', -2, 0);
                }
                $("#mo_tree_comment").html(mo_tree_comment);
            });

        });

        $("#insert").click(function () {
            message.html('');
            var data = "&name=" + $("#name").val() + "&pwd=" + $("#pwd").val() + "&email=" + $("#email").val() +
                    "&description=" + $("#description").val() + "&role=" + $("#role").val() + "&permission=" + $("#permission").val() +
                    "&blocked=" + $("#blocked").val() + "&_token={{ csrf_token() }}";
            $.ajax({
                dataType: 'json',
                url: '/admin/workers/create',
                method: "POST",
                data: data,
                success: function (data, status, xhr) {
                    message.addClass("alert-success");
                    message.removeClass("alert-danger");
                    message.html(data.responce.comment);
                    $("#userList").jqxGrid('updatebounddata');
                },
                error: function (xhr, status, errorThrown) {
                    m = '';
                    if (xhr.status == 422) {
                        $.each(xhr.responseJSON, function(field, errorText) {
                            m += errorText[0];
                        });
                        m = "Ошибка при операции сохранения на сервере - 422 (Unprocessable Entity)<br>"+ m;
                        message.addClass("alert-danger");
                        message.removeClass("alert-success");
                        message.html(m);
                    }
                }
            });
        });

        $("#save").click(function () {
            message.html('');
            var row = $('#userList').jqxGrid('getselectedrowindex');
            var rowid = $("#userList").jqxGrid('getrowid', row);
            var data = "id=" + rowid + "&name=" + $("#name").val() + "&pwd=" + $("#pwd").val() + "&email=" + $("#email").val() +
                    "&description=" + $("#description").val() + "&role=" + $("#role").val() + "&permission=" + $("#permission").val() +
                    "&blocked=" + $("#blocked").val() + "&_token={{ csrf_token() }}";
            $.ajax({
                dataType: 'json',
                url: '/admin/workers/update',
                method: "PATCH",
                data: data,
                success: function (data, status, xhr) {
                    message.addClass("alert-success");
                    message.removeClass("alert-danger");
                    message.html(data.responce.comment);
                    $("#userList").jqxGrid('updatebounddata');
                },
                error: function (xhr, status, errorThrown) {
                    m = '';
                    if (xhr.status == 422) {
                        $.each(xhr.responseJSON, function(field, errorText) {
                            m += errorText[0];
                        });
                        m = "Ошибка при операции сохранения на сервере - 422 (Unprocessable Entity)<br>" + m;
                        message.addClass("alert-danger");
                        message.removeClass("alert-success");
                        message.html(m);
                    }
                }
            });
        });
    </script>
@endsection
