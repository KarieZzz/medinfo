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
initMonitoringList = function() {
    var monSource =
    {
        datatype: "json",
        datafields: [
            { name: 'id', type: 'int' },
            { name: 'name', type: 'string' },
            { name: 'periodicity', type: 'int' },
            { name: 'accumulation', type: 'bool' },
            { name: 'album_id', type: 'int' },
            { name: 'album', map: 'album>album_name', type: 'string' },
        ],
        id: 'id',
        url: fetchmonitoring_url,
        root: null
    };
    var dataAdapter = new $.jqx.dataAdapter(monSource);
    mlist.jqxGrid(
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
                { text: 'Наименование мониторинга', datafield: 'name' , width: '270px'},
                { text: 'Периодичность', datafield: 'periodicity', width: '100px' },
                { text: 'Накопление данных', datafield: 'accumulation', columntype: 'checkbox', width: '140px' },
                { text: 'Альбом форм', datafield: 'album', width: '170px' }
            ]
        });
    mlist.on('rowselect', function (event) {
        var row = event.args.row;
        $("#name").val(row.name);
        $("#periodicity").val(row.password);
        $("#accumulation").val(row.email);
        $("#album").val(row.description);
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

initbuttons = function () {
    $('#accumulation').jqxSwitchButton({
        height: 31,
        width: 81,
        onLabel: 'Да',
        offLabel: 'Нет',
        checked: false });
};

initactions = function() {
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
};