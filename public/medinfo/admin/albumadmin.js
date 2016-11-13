/**
 * Created by shameev on 13.09.2016.
 */
initsplitter = function() {
    $("#mainSplitter").jqxSplitter(
        {
            width: '100%',
            height: '100%',
            theme: theme,
            panels:
                [
                    { size: '50%', min: '10%'},
                    { size: '50%', min: '10%'}
                ]
        }
    );
};
initdatasources = function() {
    var albumsource =
    {
        datatype: "json",
        datafields: [
            { name: 'id', type: 'int' },
            { name: 'album_name', type: 'string' },
            { name: 'default', type: 'bool' }
        ],
        id: 'id',
        url: album_url,
        root: 'album'
    };
    membersource =
    {
        datatype: "json",
        datafields: [
            { name: 'id', type: 'int' },
            { name: 'group_id', type: 'int' },
            { name: 'uname', map: 'unit>unit_name', type: 'string' },
            { name: 'ou_id', type: 'int' }
        ],
        id: 'id',
        url: member_url + currentalbum,
        root: 'member'
    };
    AlbumDataAdapter = new $.jqx.dataAdapter(albumsource);
    memberDataAdapter = new $.jqx.dataAdapter(membersource);
};
inittablelist = function() {
    agrid.jqxGrid(
        {
            width: '98%',
            height: '30%',
            theme: theme,
            localization: localize(),
            source: AlbumDataAdapter,
            columnsresize: true,
            showfilterrow: true,
            filterable: true,
            sortable: true,
            columns: [
                { text: 'Id Альбома', datafield: 'id', width: '70px' },
                { text: 'Наименование', datafield: 'album_name' , width: '400px'},
                { text: 'По умолчанию', datafield: 'default', columntype: 'checkbox', width: '70px' }
            ]
        });
    agrid.on('rowselect', function (event) {
        var row = event.args.row;
        currentalbum = row.id;
        membersource.url = member_url + currentalbum;
        mlist.jqxGrid('updatebounddata');
        $("#album_name").val(row.album_name);
        $("#default").val(row.default);
    });
    mlist.jqxGrid(
        {
            width: '98%',
            height: '65%',
            theme: theme,
            localization: localize(),
            source: memberDataAdapter,
            columnsresize: true,
            showfilterrow: true,
            filterable: true,
            sortable: true,
            columns: [
                {
                text: '№ п/п', sortable: false, filterable: false, editable: false,
                groupable: false, draggable: false, resizable: false,
                datafield: '', columntype: 'number', width: 50,
                cellsrenderer: function (row, column, value) {
                        return "<div style='margin:4px;'>" + (value + 1) + "</div>";
                    }
                },
                { text: 'МО', datafield: 'uname' , width: '580px'}
            ]
        });
};
setquerystring = function() {
    return "&album_name=" + $("#album_name").val() +
        "&default=" + ($("#default").val() ? 1 :0);
};
initalbumactions = function() {
    $("#insert").click(function () {
        var data = setquerystring();
        $.ajax({
            dataType: 'json',
            url: albumcreate_url,
            method: "POST",
            data: data,
            success: function (data, status, xhr) {
                if (typeof data.error != 'undefined') {
                    raiseError(data.message);
                } else {
                    raiseInfo(data.message);
                }
                agrid.jqxGrid('updatebounddata', 'data');
            },
            error: function (xhr, status, errorThrown) {
                $.each(xhr.responseJSON, function(field, errorText) {
                    raiseError(errorText);
                });
            }
        });
    });
    $("#save").click(function () {
        var row = agrid.jqxGrid('getselectedrowindex');
        if (row == -1) {
            raiseError("Выберите запись для изменения/сохранения данных");
            return false;
        }
        var rowid = agrid.jqxGrid('getrowid', row);
        var data = setquerystring();
        $.ajax({
            dataType: 'json',
            url: albumupdate_url + rowid,
            method: "PATCH",
            data: data,
            success: function (data, status, xhr) {
                if (typeof data.error != 'undefined') {
                    raiseError(data.message);
                } else {
                    raiseInfo(data.message);
                }
                agrid.jqxGrid('updatebounddata', 'data');
                agrid.on("bindingcomplete", function (event) {
                    var newindex = agrid.jqxGrid('getrowboundindexbyid', rowid);
                    agrid.jqxGrid('selectrow', newindex);

                });
            },
            error: function (xhr, status, errorThrown) {
                $.each(xhr.responseJSON, function(field, errorText) {
                    raiseError(errorText);
                });
            }
        });
    });
    $("#delete").click(function () {
        var row = agrid.jqxGrid('getselectedrowindex');
        if (row == -1) {
            raiseError("Выберите запись для удаления");
            return false;
        }
        var rowid = agrid.jqxGrid('getrowid', row);
        raiseConfirm("<strong>Внимание!</strong> Выбранная группа будет удалена вместе со всеми входящими в состав элементами и созданными документами.", event);
        $("#okButton").click(function () {
            hideConfirm();
            $.ajax({
                dataType: 'json',
                url: groupdelete_url + rowid,
                method: "DELETE",
                success: function (data, status, xhr) {
                    if (data.group_deleted) {
                        raiseInfo(data.message);
                        $("#form")[0].reset();
                        agrid.jqxGrid('updatebounddata', 'data');
                        agrid.jqxGrid('clearselection');

                    } else {
                        raiseError(data.message);
                    }
                },
                error: function (xhr, status, errorThrown) {
                    raiseError('Ошибка удаления группы', xhr);
                }
            });
        });
    });
};

getcheckedunits = function() {
    var ids = [];
    var checkedRows = $('#Forms').jqxGrid('getselectedrowindexes');
    for (var i = 0; i < checkedRows.length; i++) {
        // get a row.
        ids.push(checkedRows[i].uid);
    }
    return ids;
};

initmemberactions = function() {
    $("#insertmembers").click(function() {
        var selectedunits = getcheckedunits();
        var data = "&units=" + selectedunits;
        $.ajax({
            dataType: 'json',
            url: addmembers_url + currentalbum,
            method: "POST",
            data: data,
            success: function (data, status, xhr) {
                if (data.count_of_inserted > 0) {
                    raiseInfo("Добавлено учреждений в группу " + data.count_of_inserted);
                    mgrid.jqxGrid('clearselection');
                    mgrid.jqxGrid('updatebounddata');
                }
                else {
                    raiseError("Учреждения не добавлены");
                }
            },
            error: function (xhr, status, errorThrown) {
                raiseError('Ошибка сохранения данных на сервере', xhr);
            }
        });
    });
    $("#removemember").click(function() {
        var row = mgrid.jqxGrid('getselectedrowindex');
        if (row == -1) {
            raiseError("Выберите запись для удаления из списка МО, входящих в текущую группу");
            return false;
        }
        var rowid = mlist.jqxGrid('getrowid', row);
        $.ajax({
            dataType: 'json',
            url: removemember_url + rowid,
            method: "DELETE",
            success: function (data, status, xhr) {
                if (data.member_deleted) {
                    raiseInfo(data.message);
                    mgrid.jqxGrid('clearselection');
                    mgrid.jqxGrid('updatebounddata');
                }
                else {
                    raiseError("Учреждения из группы не удалены");
                }
            },
            error: function (xhr, status, errorThrown) {
                raiseError('Ошибка сохранения данных на сервере', xhr);
            }
        });
    });
}

initformlist = function() {
    var form_source =
    {
        dataType: "json",
        dataFields: [
            { name: 'id', type: 'int' },
            { name: 'form_code', type: 'string' },
            { name: 'form_name', type: 'string' }
        ],
        id: 'id',
        root: '',
        url: form_url
    };
    FormDataAdapter = new $.jqx.dataAdapter(form_source);
    $("#FormContainer").jqxPanel({width: '100%', height: '350px'});
    $("#Forms").jqxGrid(
        {
            width: '98%',
            height: '99%',
            theme: theme,
            localization: localize(),
            source: FormDataAdapter,
            columnsresize: true,
            showfilterrow: true,
            filterable: true,
            sortable: true,
            selectionmode: 'checkbox',
            columns: [
                {text: 'Код', dataField: 'form_code', width: 150},
                {text: 'Наименование', dataField: 'form_name'}
            ]
        });

};

initButtons = function() {
    $('#default').jqxSwitchButton({
        height: 31,
        width: 81,
        onLabel: 'Да',
        offLabel: 'Нет',
        checked: false });

};
