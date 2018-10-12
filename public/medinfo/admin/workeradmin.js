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
    let rolesource =
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
    wlist.jqxGrid(
        {
            width: '98%',
            height: '98%',
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
    wlist.on('rowselect', function (event) {
        uncheckAllUnits();
        let row = event.args.row;
        $("#user_name").val(row.name);
        $("#password").val(row.password);
        $("#email").val(row.email);
        $("#description").val(row.description);
        $("#role").val(row.role);
        $("#permission").val(row.permission);
        $("#blocked").val(row.blocked == 1);
        $("#ouListSave").hide();
        enableBtn();
        let scopeurl = user_scope_url + row.id;
        let units = [];
        $.ajax({
            dataType: 'json',
            url: scopeurl,
            method: "GET",
            success: function (data, status, xhr) {
                for (i = 0; i < data.length; i++) {
                    motree.jqxTreeGrid('checkRow', data[i].ou_id);
                    let row = motree.jqxTreeGrid('getRow', data[i].ou_id);
                    let ancestors = getAncestors(row);
                    units.push({ou: row, ancestors: ancestors});
                }
                showUnitList(units);
            },
            error: xhrErrorNotificationHandler
        });
    });
};

function getAncestors(row) {
    let ancestors = [];
    let current = row.parent;
    if (current === null) {
        return null;
    }
    ancestors.push(current);
    var traversAncestors = function(parent)
    {
        if (parent) {
            ancestors.push(parent);
            traversAncestors(parent.parent);
        }
    };
    traversAncestors(current.parent);
    return ancestors;
}

initmotree = function() {
    $('#setScopeWindow').jqxWindow({
        width: '800px',
        height: '630px',
        resizable: false,
        autoOpen: false,
        isModal: true,
        cancelButton: $('#cancelButton'),
        position: { x: 310, y: 125 },
    });
    let mo_source =
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
        url: fetchunits_url
    };
    let mo_dataAdapter = new $.jqx.dataAdapter(mo_source);
    motree.jqxTreeGrid(
        {
            width: '100%',
            height: '520px',
            theme: theme,
            localization: localize(),
            source: mo_dataAdapter,
            selectionMode: "singleRow",
            showToolbar: true,
            renderToolbar: motreeToolbar,
            hierarchicalCheckboxes: false,
            checkboxes: true,
            filterable: true,
            filterMode: "simple",
            columnsResize: true,
            ready: function()
            {
                motree.jqxTreeGrid('expandRow', 0);
            },
            columns: [
                { text: 'Код', dataField: 'unit_code', width: 170 },
                { text: 'Наименование', dataField: 'unit_name', width: 590 }
            ]
        });
    motree.on('filter',
        function (event)
        {
            let args = event.args;
            let filters = args.filters;
            $('#moTree').jqxTreeGrid('expandAll');
        }
    );
    motree.on('rowCheck', function (event) {
        let checked = $(this).jqxTreeGrid('getCheckedRows');
        $("#countCheckedUnits").html(checked.length);
    });
    motree.on('rowUncheck', function (event) {
        let checked = $(this).jqxTreeGrid('getCheckedRows');
        $("#countCheckedUnits").html(checked.length);
    });
};

getcheckedunits = function() {
    let ids = [];
    let checkedRows;
    let i;
    checkedRows = motree.jqxTreeGrid('getCheckedRows');
    for (i = 0; i < checkedRows.length; i++) {
        ids.push(checkedRows[i].uid);
    }
    return ids;
};

uncheckAllUnits = function() {
    let checkedUnits = motree.jqxTreeGrid('getCheckedRows');
    for (i = 0; i < checkedUnits.length; i++) {
        motree.jqxTreeGrid('uncheckRow', checkedUnits[i].uid);
    }
    $("#countCheckedUnits").html(0);
    //motree.jqxTreeGrid('collapseAll');
    //motree.jqxTreeGrid('expandRow', 0);
};

motreeToolbar = function (toolbar) {
    toolbar.append("<button type='button' id='collapseAll' class='btn btn-default btn-sm'>Свернуть все</button>");
    toolbar.append("<button type='button' id='expandAll' class='btn btn-default btn-sm'>Развернуть все</button>");
    toolbar.append("<button type='button' id='uncheckAll' class='btn btn-default btn-sm'>Снять выбор</button>");
    $("#expandAll").click(function (event) {
        motree.jqxTreeGrid('expandAll');
    });
    $("#collapseAll").click(function (event) {
        motree.jqxTreeGrid('collapseAll');
        motree.jqxTreeGrid('expandRow', 0);
    });
    $("#uncheckAll").click(function (event) {
        let checkedRows = motree.jqxTreeGrid('getCheckedRows');
        for (i = 0; i < checkedRows.length; i++) {
            motree.jqxTreeGrid('uncheckRow', checkedRows[i].uid);
        }
        $("#countCheckedUnits").html(0);
    });
};

setquerystring = function() {
    return "&user_name=" + $("#user_name").val() +
        "&password=" + $("#password").val() +
        "&email=" + $("#email").val() +
        "&description=" + $("#description").val() +
        "&role=" + $("#role").val() +
        "&permission=" + $("#permission").val() +
        "&blocked=" + ($("#blocked").val() ? 1 :0);
        //+ "&_token={{ csrf_token() }}";
};

function disableBtn() {
    sv.addClass('disabled');
    dl.addClass('disabled');
    su.addClass('disabled');
}

function enableBtn() {
    sv.removeClass('disabled');
    dl.removeClass('disabled');
    su.removeClass('disabled');
}

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
                if (typeof data.error !== 'undefined') {
                    raiseError(data.message);
                } else {
                    raiseInfo(data.message);
                }
                wlist.jqxGrid('updatebounddata', 'data');
                wlist.on("bindingcomplete", function (event) {
                    let newindex = wlist.jqxGrid('getrowboundindexbyid', data.id);
                    wlist.jqxGrid('selectrow', newindex);
                    wlist.jqxGrid('ensurerowvisible', newindex);
                });

            },
            error: xhrErrorNotificationHandler
        });
    });
    sv.click(function () {
        let row = wlist.jqxGrid('getselectedrowindex');
        if (row === -1) {
            raiseError("Выберите запись для изменения/сохранения данных");
            return false;
        }
        let rowid = wlist.jqxGrid('getrowid', row);
        $.ajax({
            dataType: 'json',
            url: workerupdate_url + rowid,
            method: "PATCH",
            data: setquerystring(),
            success: function (data, status, xhr) {
                if (typeof data.error !== 'undefined') {
                    raiseError(data.message);
                } else {
                    raiseInfo(data.message);
                }
                wlist.jqxGrid('updatebounddata', 'data');
                wlist.on("bindingcomplete", function (event) {
                    let newindex = wlist.jqxGrid('getrowboundindexbyid', rowid);
                    wlist.jqxGrid('selectrow', newindex);
                    wlist.jqxGrid('ensurerowvisible', newindex);
                });
            },
            error: xhrErrorNotificationHandler
        });
    });
    $("#delete").click(function () {
        var row = wlist.jqxGrid('getselectedrowindex');
        if (row === -1) {
            raiseError("Выберите запись для удаления");
            return false;
        }
        var rowid = wlist.jqxGrid('getrowid', row);
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
                    wlist.jqxGrid('clearselection');
                    wlist.jqxGrid('updatebounddata');
                }
                else {
                    raiseError(data.message);
                }
            },
            error: xhrErrorNotificationHandler
        });

    });
    $("#ouListSave").click(function () {
        let row = wlist.jqxGrid('getselectedrowindex');
        if (row === -1) {
            raiseError("Выберите пользователя для изменения/сохранения списка ОЕ");
            return false;
        }
        let rowid = wlist.jqxGrid('getrowid', row);
        let scope = getcheckedunits();
        let data = "worker=" + rowid + "&newscope=" + scope;
        $.ajax({
            dataType: 'json',
            url: '/admin/workers/updateuserscope',
            method: "PATCH",
            data: data,
            success: function (data, status, xhr) {
                if (typeof data.error !== 'undefined') {
                    raiseError(data.message);
                } else {
                    raiseInfo(data.message);
                }
            },
            error: xhrErrorNotificationHandler
        });
    });

    $("#pvdGen").pGenerator({
        'bind': 'click',
        'passwordElement': '#password',
        'displayElement': null,
        'passwordLength': 6,
        'uppercase': true,
        'lowercase': true,
        'numbers':   true,
        'specialChars': false
    });

    $("#setunits").click(function () {
        $("#countCheckedUnits").html(motree.jqxTreeGrid('getCheckedRows').length);
        $('#setScopeWindow').jqxWindow('open');
    });

    $("#applyList").click(function () {
        $('#setScopeWindow').jqxWindow('close');
        showUnitList(setUnitList());
        $("#ouListSave").show();
    });

    $("#cancelListChanges").click(function () {
        $('#setScopeWindow').jqxWindow('close');
        $("#ouListSave").hide();
    });
    disableBtn();
};

function showUnitList(units) {
    let list = '';
    list += '<ol>';
    for(let i = 0; i < units.length; i++) {
        let breadcrumb = [];
        if (units[i].ancestors !== null) {
            for (let j = units[i].ancestors.length - 1; j >= 0 ; j-- ) {
                breadcrumb.push(units[i].ancestors[j].unit_name);
            }
        }
        list += '<li><p><span class="text-info">' + units[i].ou.unit_name + '</span> <small>(' + breadcrumb.join(' / ') +')</small></p></li>';
    }
    list += '</ol>';
    $("#unitList").html(list);
}

function setUnitList() {
    let checkedRows = motree.jqxTreeGrid('getCheckedRows');
    let units = [];
    for (i = 0; i < checkedRows.length; i++) {
        units.push({ou: checkedRows[i], ancestors: getAncestors(checkedRows[i]) });
    }
    return units;
}