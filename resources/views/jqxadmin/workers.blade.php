@extends('jqxadmin.app')

@section('title', '<h2>Администрирование: пользователи - исполнители отчетов</h2>')

@section('content')
<div id="mainSplitter" >
    <div>
        <div id="userList" style="margin: 10px"></div>
    </div>
    <div id="formContainer" style="width: 100%; height: 100%; overflow: hidden; padding: 0 3px 3px 3px; margin-right: 10px;">
        <div id="userPropertiesForm" class="box" style="padding-bottom: 3px">
            <div class="box box-header"><h4>Данные пользователя</h4></div>
            <form id="Form">
                <table style="margin-top: 10px; margin-bottom: 10px; width: 100%;">
                    <tr><td style="text-align:right; padding-right: 15px">Имя пользователя:</td><td style="text-align:left;"><input type="text" id="name" /></td></tr>
                    <tr><td style="text-align:right; padding-right: 15px">Пароль:</td><td style="text-align:left;"><input type="text" id="password" /></td></tr>
                    <tr><td style="text-align:right; padding-right: 15px">Описание:</td><td style="text-align:left;"><input type="text" id="description" /></td></tr>
                    <tr><td style="text-align:right;padding-right: 15px">E-mail:</td><td style="text-align:left;"><input type="text" id="email" /></td></tr>
                    <tr><td style="text-align:right;padding-right: 15px">Роль:</td><td style="text-align:left;"><input type="text" id="role" /></td></tr>
                    <tr><td style="text-align:right;padding-right: 15px">Разрешения:</td><td style="text-align:left;"><input type="text" id="permission" /></td></tr>
                    <tr><td style="text-align:right;padding-right: 15px">Блокирован:</td><td style="text-align:left;"><input type="text" id="blocked" /></td></tr>
                    <tr><td></td><td style="padding-left: 35px; text-align: left;"></td></tr>
                </table>
                <div class="box box-footer" style="padding: 0 0 20px 40px">
                    <input value="Сохранить изменения" type="button" id="save" /> или
                    <input value="Вставить новую запись" type="button" id="insert" />
                </div>
            </form>
        </div>

        <div id="mo_tree_comment"></div>
        <div id="mo_selected_info" style="margin: 5px; display: none">
            <span id="mo_selected_name" class='text-info'></span>
            <input id="mo_selected_save" type='button' value='Сохранить выбор' />
        </div>
        <div id="mo_tree_container" style="margin: 5px; display: none">
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
    <script src="{{ asset('/jqwidgets/jqxdropdownbutton.js') }}"></script>
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
        var message = $("#alertmessage");
        var mo_tree_message = $("#mo_tree_comment");
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
                { name: 'password', type: 'string' },
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
                    width: '98%',
                    height: '98%',
                    theme: theme,
                    source: dataAdapter,
                    columnsresize: true,
                    showfilterrow: true,
                    filterable: true,
                    columns: [
                        { text: 'Id', datafield: 'id', width: '30px' },
                        { text: 'Имя', datafield: 'name' , width: '100px'},
                        { text: 'Пароль', datafield: 'password', width: '100px' },
                        { text: 'E-mail', datafield: 'email', width: '100px' },
                        { text: 'Описание', datafield: 'description', width: '480px'  },
                        { text: 'Роль', datafield: 'role' },
                        { text: 'Разрешения', datafield: 'permission' },
                        { text: 'Заблокирован', datafield: 'blocked' }
                    ]
                });
        $("#name").jqxInput({height: 23, theme: theme });
        $("#password").jqxInput({ height: 23, theme: theme });
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
            url: 'fetch_mo_tree/0'
        };
        var mo_dataAdapter = new $.jqx.dataAdapter(mo_source);
        $("#mo_tree_container").jqxDropDownButton({ width: 250, height: 19, theme: theme });
        $("#mo_tree_container").jqxDropDownButton('setContent', 'Выбор территории/учреждения');
        $("#moTree").jqxTreeGrid(
                {
                    width: 650,
                    height: 300,
                    theme: theme,
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
        //$("#moTree").jqxTreeGrid('expandRow', 0);
        $('#userList').on('rowselect', function (event) {
            message.html('');
            message.removeClass("alert alert-danger alert-success");
            mo_tree_message.html('');
            mo_tree_message.removeClass("well");
            $("#moTree").jqxTreeGrid('collapseAll');
            $("#moTree").jqxTreeGrid('expandRow', 0);
            $("#mo_tree_container").show();
            $("#mo_selected_info").hide();
            $("#mo_selected_name").html('');
            var row = event.args.row;
            $("#name").val(row.name);
            $("#password").val(row.password);
            $("#email").val(row.email);
            $("#description").val(row.description);
            $("#role").val(row.role);
            $("#permission").val(row.permission);
            $("#blocked").val(row.blocked);
            $("#scopes").html("");
            //$("#moTree").jqxTree('uncheckAll');
            var user_scope_url = "fetch_worker_scopes/" + row.id;
            $.getJSON( user_scope_url, function( data) {
                //console.log(data);
                var m = '';
                var mo_tree_comment = data.responce.comment;
                var items = [];
                ou_id = data.responce.scope;

                if (ou_id === 0) {
                    m += "Не указаны учреждения к которым имеет доступ пользователь.";
                }
                else {
                    unit_name = data.responce.unit_name;
                    row = $("#moTree").jqxTreeGrid('getRow', ou_id);
                    //console.log(row.parent);
                    //$("#moTree").jqxTreeGrid('selectRow', ou_id);
                    //$("#moTree").jqxTreeGrid('expandRow', row.parent.id);
                    //$("#moTree").jqxTreeGrid('expandRow', row.parent.parent.id);
                    //$("#moTree").jqxTreeGrid('expandRow', row.parent.parent.parent.id);
                    //$("#moTree").jqxTreeGrid('ensureRowVisible', ou_id);
                    //$("#moTree").jqxTreeGrid('scrollOffset', -2, 0);
                }
                mo_tree_message.addClass("well");
                mo_tree_message.html(mo_tree_comment);
            });

        });
        $('#moTree').on('filter',
                function (event)
                {
                    var args = event.args;
                    var filters = args.filters;
                    $('#moTree').jqxTreeGrid('expandAll');
                }
        );
        $("#insert").click(function () {
            message.html('');
            message.removeClass("alert alert-success alert-danger");
            var data = "&name=" + $("#name").val() + "&password=" + $("#password").val() + "&email=" + $("#email").val() +
                    "&description=" + $("#description").val() + "&role=" + $("#role").val() + "&permission=" + $("#permission").val() +
                    "&blocked=" + $("#blocked").val() + "&_token={{ csrf_token() }}";
            $.ajax({
                dataType: 'json',
                url: '/admin/workers/create',
                method: "POST",
                data: data,
                success: function (data, status, xhr) {
                    message.addClass("alert alert-success");
                    message.html(data.responce.comment);
                    $("#userList").jqxGrid('updatebounddata');
                },
                error: function (xhr, status, errorThrown) {
                    m = '';
                    $.each(xhr.responseJSON, function(field, errorText) {
                        m += errorText[0];
                    });
                    switch (xhr.status) {
                        case 422 :
                            m = "Ошибка при операции сохранения на сервере - 422 (Unprocessable Entity)<br>" + m;
                            break;
                        case 500 :
                            m = "Ошибка при операции сохранения на сервере - 500 (Internal Server Error)<br>" + m;
                            break;
                    }
                    message.addClass("alert alert-danger");
                    message.html(m);
                }
            });
        });

        $("#save").click(function () {
            message.html('');
            message.removeClass("alert alert-success alert-danger");
            var row = $('#userList').jqxGrid('getselectedrowindex');
            var rowid = $("#userList").jqxGrid('getrowid', row);
            var data = "id=" + rowid + "&name=" + $("#name").val() + "&password=" + $("#password").val() + "&email=" + $("#email").val() +
                    "&description=" + $("#description").val() + "&role=" + $("#role").val() + "&permission=" + $("#permission").val() +
                    "&blocked=" + $("#blocked").val() + "&_token={{ csrf_token() }}";
            $.ajax({
                dataType: 'json',
                url: '/admin/workers/update',
                method: "PATCH",
                data: data,
                success: function (data, status, xhr) {
                    message.addClass("alert alert-success");
                    message.html(data.responce.comment);
                    $("#userList").jqxGrid('updatebounddata');
                },
                error: function (xhr, status, errorThrown) {
                    m = '';
                    $.each(xhr.responseJSON, function(field, errorText) {
                        m += errorText[0];
                    });
                    switch (xhr.status) {
                        case 422 :
                            m = "Ошибка при операции сохранения на сервере - 422 (Unprocessable Entity)<br>" + m;
                            break;
                        case 500 :
                            m = "Ошибка при операции сохранения на сервере - 500 (Internal Server Error)<br>" + m;
                            break;
                    }
                    message.addClass("alert alert-danger");
                    message.html(m);
                }
            });
        });
        $("#mo_selected_save").jqxInput({width: 140, height: 23, theme: theme });
        $('#moTree').on('rowSelect',
                function (event)
                {

                    // event args.
                    var args = event.args;
                    // row data.
                    var row = args.row;
                    // row key.
                    var key = args.key;
                    selected = "<strong>Выбрано учреждение:</strong> " + row.unit_code + " " + row.unit_name;
                    $("#mo_selected_info").show();
                    $("#mo_selected_name").html(selected);
                });
        $("#mo_selected_save").click(function () {
            message.html('');
            message.removeClass("alert alert-success alert-danger");
            var user = $('#userList').jqxGrid('getselectedrowindex');
            var userid = $("#userList").jqxGrid('getrowid', user);
            var scopeselected = $("#moTree").jqxTreeGrid('getSelection');
            var userscope = $("#moTree").jqxTreeGrid('getKey', scopeselected[0]);

            console.log(userscope);
            var data = "userid=" + userid + "&newscope=" + userscope + "&_token={{ csrf_token() }}";
            $.ajax({
                dataType: 'json',
                url: '/admin/workers/updateuserscope',
                method: "PATCH",
                data: data,
                success: function (data, status, xhr) {
                    message.addClass("alert alert-success");
                    message.html(data.responce.comment);
                    mo_tree_message.addClass("well");
                    mo_tree_message.html("<dl><dt>Учреждение/территория, к данным котрой имеет доступ пользователь:</dt><dd> " + scopeselected[0].unit_name + "</dd></dl>" );
                    //$("#userList").jqxGrid('updatebounddata');
                },
                error: function (xhr, status, errorThrown) {
                    m = '';
                    $.each(xhr.responseJSON, function(field, errorText) {
                        m += errorText[0];
                    });
                    switch (xhr.status) {
                        case 422 :
                            m = "Ошибка при операции сохранения на сервере - 422 (Unprocessable Entity)<br>" + m;
                            break;
                        case 500 :
                            m = "Ошибка при операции сохранения на сервере - 500 (Internal Server Error)<br>" + m;
                            break;
                    }
                    message.addClass("alert alert-danger");
                    message.html(m);
                }
            });
        });
    </script>
@endsection
