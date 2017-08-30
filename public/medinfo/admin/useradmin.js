let initsplitter = function() {
    $("#mainSplitter").jqxSplitter({
        width: '100%',
        height: '100%',
        theme: theme,
        panels:
            [
                { size: '40%', min: '10%'},
                { size: '60%', min: '10%'}
            ]
    });
};

let inituserlist = function() {
    let usersource =
    {
        datatype: "json",
        datafields: [
            { name: 'id', type: 'int' },
            { name: 'name', type: 'string' },
            { name: 'email', type: 'string' }
        ],
        id: 'id',
        url: fetchusers_url,
        root: null
    };
    let dataAdapter = new $.jqx.dataAdapter(usersource);
    ulist.jqxGrid(
        {
            width: '98%',
            height: '90%',
            theme: theme,
            localization: localize(),
            source: dataAdapter,
            columnsresize: true,
            showfilterrow: true,
            filterable: true,
            sortable: true,
            columns: [
                { text: 'Id', datafield: 'id', width: '70px' },
                { text: 'Имя', datafield: 'name' , width: '60%'},
                { text: 'E-mail', datafield: 'email', width: '220px' }
            ]
        });
    ulist.on('rowselect', function (event) {
        let row = event.args.row;
        $("#name").val(row.name);
        $("#email").val(row.email);
        $("#role").val(row.role);
        $("#password").val('');
    });
};

let setquerystring = function() {
    return "&name=" + $("#name").val() +
        "&password=" + $("#password").val() +
        "&email=" + $("#email").val();
};

let initactions = function() {
    $("#insert").click(function () {
        $.ajax({
            dataType: 'json',
            url: user_url,
            method: "POST",
            data: setquerystring(),
            success: function (data, status, xhr) {
                raiseInfo(data);
                ulist.jqxGrid('updatebounddata');
            },
            error: function (xhr, status, errorThrown) {
                m = 'Данные не сохранены. ';
                $.each(xhr.responseJSON, function(field, errorText) {
                    m += errorText[0] + ' ';
                });
                raiseError(m, xhr);
            }
        });
    });
    $("#save").click(function () {
        let row = ulist.jqxGrid('getselectedrowindex');
        if (row === -1) {
            raiseError("Выберите запись для изменения/сохранения данных");
            return false;
        }
        let rowid = ulist.jqxGrid('getrowid', row);
        $.ajax({
            dataType: 'json',
            url: user_url + '/' + rowid,
            method: "PATCH",
            data: setquerystring(),
            success: function (data, status, xhr) {
                raiseInfo(data);
                ulist.jqxGrid('updatebounddata');
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
        let row = ulist.jqxGrid('getselectedrowindex');
        if (row === -1) {
            raiseError("Выберите запись для удаления");
            return false;
        }
        let rowid = ulist.jqxGrid('getrowid', row);
        let confirm_text = 'Подтвердите удаление пользователя Id' + rowid ;
        if (!confirm(confirm_text)) {
            return false;
        }
        $.ajax({
            dataType: 'json',
            url: user_url + '/' + rowid,
            method: "DELETE",
            success: function (data, status, xhr) {
                raiseInfo(data);
                $("#form")[0].reset();
                ulist.jqxGrid('clearselection');
                ulist.jqxGrid('updatebounddata');
            },
            error: function (xhr, status, errorThrown) {
                raiseError('Ошибка сохранения данных на сервере', xhr);
            }
        });

    });
};