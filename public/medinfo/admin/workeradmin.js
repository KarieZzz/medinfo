initsplitter = function() {
    $("#mainSplitter").jqxSplitter({
        width: '100%',
        height: '100%',
        theme: theme,
        panels:
            [
                { size: '50%', min: '10%'},
                { size: '50%', min: '10%'}
            ]
    });
};
initdropdowns = function() {
    var rolesource =
    {
        datatype: "json",
        datafields: [
            { name: 'code' },
            { name: 'name' }
        ],
        id: 'code',
        localdata: roles
    };
    roleDataAdapter = new $.jqx.dataAdapter(rolesource);
};
inituserlist = function() {
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
            height: '90%',
            theme: theme,
            localization: localize(),
            source: dataAdapter,
            columnsresize: true,
            showfilterrow: true,
            filterable: true,
            columns: [
                { text: 'Id', datafield: 'id', width: '30px' },
                { text: 'Имя', datafield: 'name' , width: '100px'},
                { text: 'Пароль', datafield: 'password', width: '100px' },
                { text: 'E-mail', datafield: 'email', width: '120px' },
                { text: 'Описание', datafield: 'description', width: '440px'  },
                { text: 'Роль', datafield: 'role', width: '40px' },
                { text: 'Разрешения', datafield: 'permission', width: '40px' },
                { text: 'Заблокирован', datafield: 'blocked', width: '40px' }
            ]
        });
    $('#userList').on('rowselect', function (event) {
        mo_tree_message.html('');
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
        $("#blocked").val(row.blocked == 1);
        $("#scopes").html("");
        var user_scope_url = "fetch_worker_scopes/" + row.id;
        $.getJSON( user_scope_url, function( data) {
            var m = '';
            var mo_tree_comment = data.responce.comment;
            var items = [];
            ou_id = data.responce.scope;

            if (ou_id === 0) {
                m += " Не указаны.";
            }
            else {
                unit_name = data.responce.unit_name;
                row = $("#moTree").jqxTreeGrid('getRow', ou_id);
            }
            mo_tree_message.html(mo_tree_comment);
        });

    });
};
initmotree = function() {
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
    $("#mo_tree_container").jqxDropDownButton({ width: 250, height: 31, theme: theme });
    $("#mo_tree_container").jqxDropDownButton('setContent', 'Выбор территории/учреждения');
    $("#moTree").jqxTreeGrid(
        {
            width: 680,
            height: 280,
            theme: theme,
            localization: localize(),
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
    $('#moTree').on('filter',
        function (event)
        {
            var args = event.args;
            var filters = args.filters;
            $('#moTree').jqxTreeGrid('expandAll');
        }
    );
    $('#moTree').on('rowSelect', function (event) {
        var args = event.args;
        var row = args.row;
        var key = args.key;
        selected = "<strong>Выбрано учреждение:</strong> " + row.unit_code + " " + row.unit_name;
        $("#mo_selected_info").show();
        $("#mo_selected_name").html(selected);
    });
};

setquerystring = function() {
    return "&name=" + $("#name").val() +
        "&password=" + $("#password").val() +
        "&email=" + $("#email").val() +
        "&description=" + $("#description").val() +
        "&role=" + $("#role").val() +
        "&permission=" + $("#permission").val() +
        "&blocked=" + ($("#blocked").val() ? 1 :0);
        //+ "&_token={{ csrf_token() }}";
};

initactions = function() {
    $('#blocked').jqxSwitchButton({
        height: 31,
        width: 81,
        onLabel: 'Да',
        offLabel: 'Нет',
        checked: false });
    $("#insert").click(function () {
        $.ajax({
            dataType: 'json',
            url: '/admin/workers/create',
            method: "POST",
            data: setquerystring(),
            success: function (data, status, xhr) {
                raiseInfo(data.responce.comment);
                $("#userList").jqxGrid('updatebounddata');
            },
            error: function (xhr, status, errorThrown) {
                m = 'Данные не сохранены. ';
                $.each(xhr.responseJSON, function(field, errorText) {
                    m += errorText[0];
                });
                raiseError(m, xhr);
            }
        });
    });
    $("#save").click(function () {
        var row = $('#userList').jqxGrid('getselectedrowindex');
        if (row == -1) {
            raiseError("Выберите запись для изменения/сохранения данных");
            return false;
        }
        var rowid = $("#userList").jqxGrid('getrowid', row);
        $.ajax({
            dataType: 'json',
            url: workerupdate_url + rowid,
            method: "PATCH",
            data: setquerystring(),
            success: function (data, status, xhr) {
                raiseInfo(data.responce.comment);
                $("#userList").jqxGrid('updatebounddata');
            },
            error: function (xhr, status, errorThrown) {
                m = 'Данные не сохранены. ';
                $.each(xhr.responseJSON, function(field, errorText) {
                    m += errorText[0];
                });
                raiseError(m, xhr);
            }
        });
    });
    $("#delete").click(function () {
        var row = $('#userList').jqxGrid('getselectedrowindex');
        if (row == -1) {
            raiseError("Выберите запись для удаления");
            return false;
        }
        var rowid = $("#userList").jqxGrid('getrowid', row);
        var confirm_text = 'Подтвердите удаление пользователя Id' + rowid ;
        if (!confirm(confirm_text)) {
            return false;
        }
        $.ajax({
            dataType: 'json',
            url: workerdelete_url + rowid,
            method: "DELETE",
            success: function (data, status, xhr) {
                if (data.worker_deleted) {
                    raiseInfo(data.message);
                    $('#userList').jqxGrid('clearselection');
                    $('#userList').jqxGrid('updatebounddata');
                }
                else {
                    raiseError(data.message);
                }
            },
            error: function (xhr, status, errorThrown) {
                raiseError('Ошибка сохранения данных на сервере', xhr);
            }
        });

    });
    $("#mo_selected_save").click(function () {
        var user = $('#userList').jqxGrid('getselectedrowindex');
        var userid = $("#userList").jqxGrid('getrowid', user);
        var scopeselected = $("#moTree").jqxTreeGrid('getSelection');
        var userscope = $("#moTree").jqxTreeGrid('getKey', scopeselected[0]);
        var data = "userid=" + userid + "&newscope=" + userscope;
        $.ajax({
            dataType: 'json',
            url: '/admin/workers/updateuserscope',
            method: "PATCH",
            data: data,
            success: function (data, status, xhr) {
                mo_tree_message.html(scopeselected[0].unit_name );
                raiseInfo(data.responce.comment)
            },
            error: function (xhr, status, errorThrown) {
                m = 'Даные не сохранены. ';
                $.each(xhr.responseJSON, function(field, errorText) {
                    m += errorText[0];
                });
                raiseError(m, xhr);
            }
        });
    });
};